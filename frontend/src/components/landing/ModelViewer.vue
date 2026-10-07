<template>
    <div
        ref="container"
        class="model-viewer relative aspect-[4/3] w-full"
        role="img"
        :aria-label="'Model 3D ' + label"
    >
        <div
            v-if="!ready"
            aria-hidden="true"
            class="absolute inset-0 grid place-items-center"
        >
            <div class="h-3/4 w-3/4 animate-pulse rounded-full bg-primary-100 blur-3xl"></div>
        </div>

        <p
            v-show="interactive"
            class="pointer-events-none absolute right-3 bottom-2 text-[11px] text-gray-400"
        >
            Geser untuk memutar
        </p>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue';
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: 'produk',
    },
});

const container = useTemplateRef('container');
const ready = ref(false);
const interactive = ref(false);

const SPIN_SPEED = 0.28;

let renderer;
let scene;
let camera;
let controls;
let pivot;
let frameId = 0;
let resizeObserver;
let intersectionObserver;
let visible = true;
let spinning = true;
let disposed = false;
let clock;

function stopLoop() {
    if (frameId) {
        cancelAnimationFrame(frameId);
        frameId = 0;
    }
}

function startLoop() {
    if (disposed || frameId) return;
    frameId = requestAnimationFrame(tick);
}

function tick() {
    frameId = 0;
    if (disposed) return;
    if (!visible) return;

    const delta = clock.getDelta();

    if (spinning && pivot) {
        pivot.rotation.y += SPIN_SPEED * delta;
    }

    controls.update();
    renderer.render(scene, camera);
    frameId = requestAnimationFrame(tick);
}

function resize() {
    const el = container.value;
    if (!el || !renderer || !camera) return;

    const width = el.clientWidth;
    const height = el.clientHeight;
    if (width === 0 || height === 0) return;

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(width, height, false);
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
}

function frameModel(object) {
    const box = new THREE.Box3().setFromObject(object);
    if (box.isEmpty()) return;

    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());

    const maxDim = Math.max(size.x, size.y, size.z) || 1;
    const scale = 2 / maxDim;

    object.scale.setScalar(scale);
    object.position.copy(center).multiplyScalar(-scale);

    const radius = 0.5 * Math.sqrt(size.x ** 2 + size.y ** 2 + size.z ** 2) * scale;
    const vFov = THREE.MathUtils.degToRad(camera.fov);
    const hFov = 2 * Math.atan(Math.tan(vFov / 2) * camera.aspect);
    const distance = Math.max(radius / Math.sin(vFov / 2), radius / Math.sin(hFov / 2)) * 1.15;

    const direction = new THREE.Vector3(0.7, 0.32, 1).normalize();
    camera.position.copy(direction).multiplyScalar(distance);
    camera.near = Math.max(distance / 100, 0.01);
    camera.far = distance * 100;
    camera.updateProjectionMatrix();

    controls.target.set(0, 0, 0);
    controls.update();
}

function loadModel() {
    new GLTFLoader().load(
        props.src,
        (gltf) => {
            if (disposed) return;

            const model = gltf.scene;
            model.traverse((node) => {
                if (node.isMesh) node.castShadow = false;
            });

            pivot = new THREE.Group();
            pivot.add(model);
            scene.add(pivot);

            frameModel(pivot);
            ready.value = true;
            startLoop();
        },
        undefined,
        (error) => {
            console.warn('[ModelViewer] gagal memuat model 3D', error);
        },
    );
}

onMounted(() => {
    const el = container.value;
    if (!el) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    interactive.value = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    spinning = !reducedMotion;

    try {
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    } catch (error) {
        console.warn('[ModelViewer] WebGL tidak tersedia', error);
        return;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.domElement.setAttribute('aria-hidden', 'true');
    el.appendChild(renderer.domElement);

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(38, 1, 0.1, 1000);

    scene.add(new THREE.HemisphereLight(0xffffff, 0xdbeafe, 2.4));

    const key = new THREE.DirectionalLight(0xffffff, 2.4);
    key.position.set(4, 6, 5);
    scene.add(key);

    const fill = new THREE.DirectionalLight(0xc7d2fe, 0.9);
    fill.position.set(-5, 2, -4);
    scene.add(fill);

    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan = false;
    controls.enableZoom = false;
    controls.rotateSpeed = 0.55;
    controls.minPolarAngle = Math.PI / 6;
    controls.maxPolarAngle = Math.PI / 2.05;
    controls.enabled = interactive.value;
    renderer.domElement.style.touchAction = interactive.value ? 'none' : 'auto';

    clock = new THREE.Clock();

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(el);
    resize();

    intersectionObserver = new IntersectionObserver(
        ([entry]) => {
            visible = entry.isIntersecting;
            if (visible) startLoop();
            else stopLoop();
        },
        { threshold: 0 },
    );
    intersectionObserver.observe(el);

    loadModel();
});

onBeforeUnmount(() => {
    disposed = true;
    stopLoop();
    resizeObserver?.disconnect();
    intersectionObserver?.disconnect();
    controls?.dispose();

    if (scene) {
        scene.traverse((node) => {
            node.geometry?.dispose();
            const materials = Array.isArray(node.material) ? node.material : [node.material];
            materials.forEach((material) => {
                if (!material) return;
                Object.values(material).forEach((value) => {
                    if (value?.isTexture) value.dispose();
                });
                material.dispose();
            });
        });
    }

    renderer?.dispose();
    renderer?.domElement?.remove();
});
</script>

<style scoped>
.model-viewer :deep(canvas) {
    display: block;
    width: 100%;
    height: 100%;
}
</style>
