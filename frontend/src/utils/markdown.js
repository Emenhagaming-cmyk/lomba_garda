function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function renderInline(line) {
    return line
        .replace(/`([^`]+)`/g, '<code class="rounded bg-gray-200 px-1 py-0.5 text-[0.85em]">$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[^*])\*([^*]+)\*/g, '$1<em>$2</em>');
}

export function renderMarkdown(text) {
    const lines = escapeHtml(text).split(/\r?\n/);
    let html = '';
    let listType = null;

    const closeList = () => {
        if (listType) {
            html += `</${listType}>`;
            listType = null;
        }
    };

    for (const rawLine of lines) {
        const line = rawLine.trim();

        if (line === '') {
            closeList();
            continue;
        }

        const heading = line.match(/^(#{1,6})\s+(.*)$/);

        if (heading) {
            closeList();
            const size = heading[1].length === 1 ? ' text-lg' : '';
            html += `<p class="font-bold${size}">${renderInline(heading[2])}</p>`;
            continue;
        }

        const quote = line.match(/^&gt;\s?(.*)$/);

        if (quote) {
            closeList();
            html += `<blockquote class="border-l-2 border-primary-600 pl-3 italic text-gray-600">${renderInline(quote[1])}</blockquote>`;
            continue;
        }

        const ordered = line.match(/^\d+[.)]\s+(.*)$/);
        const unordered = line.match(/^[-*•]\s+(.*)$/);

        if (ordered) {
            if (listType !== 'ol') {
                closeList();
                html += '<ol class="list-decimal space-y-1 pl-5">';
                listType = 'ol';
            }
            html += `<li>${renderInline(ordered[1])}</li>`;
            continue;
        }

        if (unordered) {
            if (listType !== 'ul') {
                closeList();
                html += '<ul class="list-disc space-y-1 pl-5">';
                listType = 'ul';
            }
            html += `<li>${renderInline(unordered[1])}</li>`;
            continue;
        }

        closeList();
        html += `<p>${renderInline(line)}</p>`;
    }

    closeList();

    return html;
}
