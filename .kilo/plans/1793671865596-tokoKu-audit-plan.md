# TokoKu Business OS - Directory Audit Plan

## Overview
This document outlines the audit of the TokoKu Business OS codebase to understand its structure, components, and implementation details.

## Project Structure

### Root Directory
- **backend/**: Laravel 12 application (PHP 8.2+)
- **frontend/**: Vue 3 SPA application (Vite 7+)
- **docs/**: Documentation including setup guides and requirements
- **plans/**: Empty directory for plan files (to be created)

### Backend (Laravel) Structure
- **app/**: Contains Http, Models, Providers directories
- **routes/**: 
  - api.php: API routes (JSON responses, auth:sanctum group)
  - web.php: Web routes (SPA fallback)
- **config/**: Configuration files
- **database/**: Migrations and seeds
- **public/**: Public assets including build outputs
- **resources/**: Views and assets
- **storage/**: Logs and storage files
- **tests/**: PHPUnit tests (21 total tests)

### Frontend (Vue 3) Structure
- **src/**: 
  - api/: Contains client.js (axios wrapper)
  - assets/: Contains images, fonts, etc.
  - components/: Reusable components (PageHeader, Modal, etc.)
  - layouts/: Layout components (AppLayout, Sidebar)
  - pages/: All page components (Dashboard, Products, Sales, etc.)
  - router/: Vue router configuration
  - stores/: Pinia stores for state management
  - utils/: Utility functions
  - app.css: Main CSS with Tailwind v4
  - app.js: Main entry point

### Key Features Identified
1. **Single Entry Transaction Flow**: One input (sale) affects multiple systems (stock, finance, customer history)
2. **Offline-First**: Transactions can be saved locally when offline and synced later
3. **Multi-Tenancy**: Single user = single business (business_id scoping)
4. **API Envelope**: Standardized JSON responses with success/error formats
5. **Stateful Sessions**: Session-based authentication (Sanctum cookies)

## Critical Files to Examine

### Backend
- `backend/routes/api.php`: API route definitions
- `backend/app/Http/Controllers/`: Business logic controllers
- `backend/app/Models/`: Eloquent models
- `backend/database/migrations/`: Database schema
- `frontend/src/api/client.js`: API client implementation

### Frontend
- `frontend/src/pages/`: Page components
- `frontend/src/components/`: Reusable UI components
- `frontend/src/router/index.js`: Route configuration
- `frontend/src/stores/`: Pinia stores
- `frontend/src/app.css`: Tailwind CSS configuration

## Design Principles
1. **Clean, Calm, Warm**: Dark text on warm krim background with one primary color (terakota/clay)
2. **Quicksand Font**: Self-hosted 300-700 weight range
3. **Stateful API Calls**: Must include Origin and Referer headers for session management
4. **Single Entry**: One transaction input affects multiple systems simultaneously
5. **Offline Support**: Local storage with sync queue mechanism

## Open Questions & Next Steps
1. Need to examine specific controller implementations to understand business logic flow
2. Review the API client implementation to understand request patterns
3. Examine the database schema to understand relationships and business_id scoping
4. Check the onboarding process flow based on PRD requirements
5. Verify the offline transaction synchronization mechanism

## Plan
1. First, examine key controllers to understand business logic
2. Review API client implementation 
3. Examine database schema and models
4. Review page components to understand UI structure
4. Check the onboarding flow implementation
5. Document findings and create detailed implementation plan

I'll start by examining the key controllers to understand the business logic flow.