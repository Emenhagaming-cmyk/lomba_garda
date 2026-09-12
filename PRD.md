# UMKM Predictive Operations
## AGENTS + PRD — Master Product & Engineering Specification

> **Document status:** Working specification / implementation blueprint  
> **Product:** UMKM Predictive Operations  
> **Primary market:** Indonesian UMKM, initially focused on food & beverage businesses  
> **Document purpose:** Single source of truth for AI coding agents and the development team.

---

# TABLE OF CONTENTS

1. [Project Identity](#1-project-identity)
2. [Mission](#2-mission)
3. [Product Vision](#3-product-vision)
4. [Problem Statement](#4-problem-statement)
5. [Target Users](#5-target-users)
6. [Product Principles](#6-product-principles)
7. [Product Scope](#7-product-scope)
8. [Core User Journey](#8-core-user-journey)
9. [Functional Requirements](#9-functional-requirements)
10. [Feature Prioritization](#10-feature-prioritization)
11. [Domain Model](#11-domain-model)
12. [Inventory Rules](#12-inventory-rules)
13. [Sales Rules](#13-sales-rules)
14. [Purchase Rules](#14-purchase-rules)
15. [Waste Rules](#15-waste-rules)
16. [Forecast Engine](#16-forecast-engine)
17. [Reorder Recommendation Engine](#17-reorder-recommendation-engine)
18. [Business Insight Engine](#18-business-insight-engine)
19. [Dashboard Requirements](#19-dashboard-requirements)
20. [Page & Route Specification](#20-page--route-specification)
21. [UX Requirements](#21-ux-requirements)
22. [Technical Architecture](#22-technical-architecture)
23. [Database Requirements](#23-database-requirements)
24. [API Requirements](#24-api-requirements)
25. [Authentication & Authorization](#25-authentication--authorization)
26. [Multi-Tenancy](#26-multi-tenancy)
27. [Security](#27-security)
28. [Testing](#28-testing)
29. [Seed & Demo Data](#29-seed--demo-data)
30. [Analytics & KPI](#30-analytics--kpi)
31. [Performance](#31-performance)
32. [Accessibility](#32-accessibility)
33. [Git & Development Workflow](#33-git--development-workflow)
34. [AI Coding Agent Constitution](#34-ai-coding-agent-constitution)
35. [Agent Operating Procedure](#35-agent-operating-procedure)
36. [Definition of Done](#36-definition-of-done)
37. [Competition Alignment](#37-competition-alignment)
38. [Demo Strategy](#38-demo-strategy)
39. [Risks](#39-risks)
40. [Roadmap](#40-roadmap)
41. [Final Release Gate](#41-final-release-gate)

---

# 1. PROJECT IDENTITY

## 1.1 Working Name

**UMKM Predictive Operations**

Potential future product name:

> **NADI — Predictive Operations for UMKM**

The name may be changed later without changing the product architecture.

## 1.2 Product Category

- Business Operations
- Inventory Intelligence
- Purchasing Intelligence
- SME Digital Transformation
- Business Decision Support

## 1.3 Product Type

A responsive web application that helps UMKM owners manage operational data
and turn it into actionable inventory and purchasing recommendations.

## 1.4 Product Thesis

The product transforms:

```text
SALES
  ↓
USAGE
  ↓
INVENTORY
  ↓
FORECAST
  ↓
RISK DETECTION
  ↓
REORDER RECOMMENDATION
  ↓
PURCHASE ORDER
  ↓
BUSINESS INSIGHT
```

The application is intentionally **not a full ERP**.

The primary problem is:

> Help UMKM owners know what stock is running out, what stock is being
> wasted, when they should reorder, and how much they should purchase.

---

# 2. MISSION

The development team must optimize for:

1. Real functionality
2. Clear business value
3. Explainable intelligence
4. Reliable data flow
5. Excellent UX
6. Clean architecture
7. Demonstrable implementation
8. Focused scope

Do not optimize for:

- feature quantity
- unnecessary technology complexity
- AI buzzwords
- excessive animation
- fake AI
- fake analytics
- microservices without a real need
- incomplete features

---

# 3. PRODUCT VISION

## 3.1 Vision

Enable a small business owner to move from:

> "Kayaknya stok hampir habis."

to:

> "Tomat diprediksi habis dalam 2 hari. Supplier membutuhkan rata-rata
> 1 hari. Disarankan membeli 8 kg."

The system must transform raw operational data into understandable decisions.

## 3.2 Product Promise

The application should answer:

```text
WHAT?
What is happening?

WHY?
Why is it happening?

WHEN?
When will action be needed?

HOW MUCH?
How much should be purchased?

WHAT NEXT?
What action should the owner take?
```

---

# 4. PROBLEM STATEMENT

## 4.1 Primary Problems

Many small businesses manage inventory using:

- memory
- spreadsheets
- manual notes
- physical inspection
- intuition
- inconsistent stock records

This causes:

### Stockout

Important ingredients run out before the next supplier delivery.

### Overstock

The business purchases too much stock.

### Waste

Perishable inventory expires or becomes unusable.

### Poor Purchasing Decisions

Owners do not know:

- when to buy
- how much to buy
- which supplier to use
- whether current stock is sufficient

### Lack of Operational Visibility

The owner sees sales numbers but does not necessarily understand the
relationship between:

```text
sales → consumption → inventory → purchasing → waste
```

---

# 5. TARGET USERS

## 5.1 Primary Persona — UMKM Owner

Example:

```text
Name:
Budi

Business:
Small food & beverage outlet

Team:
1 owner
2–5 staff

Current workflow:
- records sales manually or with a simple POS
- checks inventory physically
- purchases based on intuition
- sometimes overbuys
- sometimes runs out of ingredients

Main pain:
"I know the business is running, but I don't know what is actually
causing my stock problems."
```

## 5.2 Secondary Persona — Staff / Operator

Responsibilities:

- record sales
- receive purchases
- update stock
- record waste
- inspect inventory

Restrictions:

- should not modify sensitive business settings
- should not change forecasting configuration without permission
- should not access owner-only analytics where inappropriate

---

# 6. PRODUCT PRINCIPLES

## 6.1 Explainability Over Hype

Every recommendation must explain **why** it exists.

Bad:

```text
AI recommends buying 10 units.
```

Good:

```text
Recommended purchase: 10 units

Because:
- average daily usage = 2 units
- supplier lead time = 2 days
- safety stock = 4 units
- current stock = 1 unit
- incoming stock = 0 units
```

---

## 6.2 Real Data Flow

Features must connect to the real domain model.

Required relationship:

```text
Sale
 ↓
Recipe / BOM
 ↓
Ingredient Consumption
 ↓
Stock Movement
 ↓
Inventory Balance
 ↓
Forecast
 ↓
Reorder Recommendation
 ↓
Purchase Order
 ↓
Purchase Receiving
 ↓
Inventory
```

---

## 6.3 Deterministic First

The MVP must work without:

- external AI APIs
- GPU
- machine-learning infrastructure
- third-party inference
- internet-dependent intelligence

Machine learning or generative AI may be introduced later.

The core product must remain useful without it.

---

## 6.4 Business Value First

Every major feature must answer:

> What business decision does this help the owner make?

If a feature does not improve:

- inventory control
- purchasing
- waste reduction
- operational visibility
- decision making

it should not be part of MVP.

---

# 7. PRODUCT SCOPE

## 7.1 MVP Modules

```text
1. Authentication
2. Business Setup
3. Product Management
4. Ingredient Management
5. Recipe / BOM
6. Sales Transaction
7. Inventory
8. Stock Movement
9. Supplier
10. Purchase Order
11. Purchase Receiving
12. Waste Tracking
13. Forecast Engine
14. Reorder Recommendation
15. Dashboard
16. Business Insights
```

## 7.2 Explicitly Out of MVP

Do not implement unless specifically approved:

- social media
- marketplace
- customer chat
- payroll
- full accounting
- HR suite
- delivery management
- ecommerce storefront
- loyalty program
- complex CRM
- multi-country taxation
- multi-outlet complexity
- autonomous purchasing

---

# 8. CORE USER JOURNEY

## 8.1 Happy Path

```text
Login
 ↓
Dashboard
 ↓
View inventory risk
 ↓
Open critical ingredient
 ↓
View forecast
 ↓
View recommendation
 ↓
Approve recommendation
 ↓
Create purchase order
 ↓
Receive goods
 ↓
Inventory updated
 ↓
Dashboard reflects new state
```

## 8.2 Core Product Loop

```text
SELL
 ↓
CONSUME STOCK
 ↓
OBSERVE
 ↓
PREDICT
 ↓
RECOMMEND
 ↓
PURCHASE
 ↓
RECEIVE
 ↓
LEARN
 ↓
SELL AGAIN
```

---

# 9. FUNCTIONAL REQUIREMENTS

## 9.1 Authentication

### Requirements

- Login
- Logout
- Session handling
- Protected routes
- Role-based authorization

### Acceptance Criteria

```text
[ ] Unauthenticated users cannot access protected application pages.
[ ] Authenticated users can log out.
[ ] Invalid credentials produce a safe user-facing error.
[ ] Sessions are validated server-side.
```

---

## 9.2 Business Setup

### Requirements

- Business name
- Business type
- Currency
- Default units
- Basic business settings

### Acceptance Criteria

```text
[ ] Owner can create a business.
[ ] Business data is scoped to the authenticated owner.
[ ] Business settings can be updated.
```

---

## 9.3 Product Management

Product represents something the business sells.

Fields:

```text
id
business_id
name
sku
selling_price
status
created_at
updated_at
```

Actions:

- create
- edit
- archive
- search
- filter

---

## 9.4 Ingredient Management

Ingredient represents a stock item consumed during production.

Fields:

```text
id
business_id
name
sku
unit
unit_cost
minimum_stock
status
created_at
updated_at
```

Supported units may include:

```text
kg
gram
liter
ml
pcs
box
pack
```

---

## 9.5 Recipe / BOM

A recipe connects a product to ingredients.

Example:

```text
Es Teh
 ├── Tea: 10 gram
 ├── Sugar: 15 gram
 └── Ice: 100 gram
```

Requirements:

```text
[ ] Product can have multiple ingredients.
[ ] Ingredient quantity per product is explicit.
[ ] Recipe changes are auditable where practical.
[ ] Recipe cannot reference an archived ingredient without explicit handling.
```

---

## 9.6 Sales Transaction

A sale contains:

```text
Sale
 ├── Sale Items
 │    ├── Product
 │    ├── Quantity
 │    └── Selling Price
```

Completing a sale must trigger inventory consumption.

---

## 9.7 Inventory

Inventory must show:

- current stock
- unit
- stock value
- status
- expected depletion
- reorder status
- stock movement history

Statuses:

```text
HEALTHY
LOW
CRITICAL
OUT_OF_STOCK
```

---

## 9.8 Supplier

Fields:

```text
id
business_id
name
contact_name
phone
email
address
default_lead_time_days
status
```

Supplier items:

```text
supplier_id
ingredient_id
purchase_unit
unit_price
lead_time_days
minimum_order_quantity
```

---

## 9.9 Purchase Order

Lifecycle:

```text
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
ORDERED
 ↓
PARTIALLY_RECEIVED
 ↓
RECEIVED
```

Cancellation:

```text
DRAFT → CANCELLED
SUBMITTED → CANCELLED
```

Creating a PO does **not** increase inventory.

Only receiving goods increases inventory.

---

## 9.10 Waste Tracking

Waste record:

```text
ingredient
quantity
reason
unit_cost
total_cost
notes
created_by
created_at
```

Reasons:

```text
EXPIRED
DAMAGED
SPOILED
PRODUCTION_ERROR
OTHER
```

Recording waste creates a negative stock movement.

---

# 10. FEATURE PRIORITIZATION

## P0 — MUST HAVE

```text
Authentication
Business Setup
Product
Ingredient
Recipe
Sales
Inventory
Stock Movement
Supplier
Purchase Order
Purchase Receiving
Waste
Forecast
Reorder Recommendation
Dashboard
Business Insights
```

## P1 — SHOULD HAVE

```text
Advanced analytics
Inventory turnover
Gross margin
Supplier comparison
Purchase history analysis
What-if simulation
CSV export
Configurable forecast period
```

## P2 — NICE TO HAVE

```text
Multi-outlet
Automated notifications
WhatsApp integration
Advanced ML
Seasonality model
Supplier optimization
Automatic PO generation
AI assistant
Accounting integration
```

---

# 11. DOMAIN MODEL

Core entities:

```text
User
Business
BusinessMember
Product
Ingredient
Recipe
RecipeItem
Supplier
SupplierItem
Inventory
StockMovement
Sale
SaleItem
PurchaseOrder
PurchaseOrderItem
WasteRecord
Forecast
ReorderRecommendation
BusinessInsight
```

Relationship:

```text
Business
 ├── Users / Members
 ├── Products
 │    └── Recipes
 │         └── Ingredients
 │
 ├── Suppliers
 │    └── SupplierItems
 │
 ├── Sales
 │    └── SaleItems
 │
 ├── PurchaseOrders
 │    └── PurchaseOrderItems
 │
 ├── Inventory
 │    └── StockMovements
 │
 ├── WasteRecords
 ├── Forecasts
 ├── ReorderRecommendations
 └── BusinessInsights
```

---

# 12. INVENTORY RULES

## 12.1 Event-Oriented Inventory

Do not depend solely on manually editable stock numbers.

Conceptual calculation:

```text
Opening Balance
+ Purchase Received
+ Adjustment In
- Sales Consumption
- Waste
- Adjustment Out
+ Returns
= Current Stock
```

## 12.2 Stock Movement Types

```text
OPENING_BALANCE
PURCHASE_RECEIVED
SALE_CONSUMPTION
WASTE
ADJUSTMENT_IN
ADJUSTMENT_OUT
RETURN
```

## 12.3 Stock Movement Structure

```text
id
business_id
ingredient_id
type
quantity
unit_cost
reference_type
reference_id
created_at
created_by
```

## 12.4 Immutability

Do not silently overwrite historical stock movements.

Bad:

```text
Edit historical movement directly.
```

Good:

```text
Create a compensating adjustment movement.
```

This protects:

- auditability
- forecasting
- reporting
- debugging

---

# 13. SALES RULES

When a sale is completed:

```text
Sale
 ↓
SaleItems
 ↓
Recipe
 ↓
RecipeItems
 ↓
Ingredient Consumption
 ↓
StockMovement(SALE_CONSUMPTION)
```

Example:

```text
Product:
Es Teh

Recipe:
Tea   = 10 gram
Sugar = 15 gram
Ice   = 100 gram

Quantity sold:
5
```

Result:

```text
Tea   = -50 gram
Sugar = -75 gram
Ice   = -500 gram
```

## 13.1 Atomicity

Sale completion must be atomic:

```text
create sale
+
create sale items
+
create stock movements
```

If one operation fails, rollback the transaction.

---

# 14. PURCHASE RULES

## 14.1 PO Lifecycle

```text
DRAFT
SUBMITTED
APPROVED
ORDERED
PARTIALLY_RECEIVED
RECEIVED
CANCELLED
```

## 14.2 Inventory Rule

```text
Create PO:
NO inventory change

Receive PO:
YES inventory increase
```

## 14.3 Partial Receiving

The system must support receiving less than the ordered quantity.

Example:

```text
Ordered:
20 kg

Received:
12 kg

Remaining:
8 kg
```

Status:

```text
PARTIALLY_RECEIVED
```

---

# 15. WASTE RULES

When waste is recorded:

```text
Waste Record
 ↓
Negative Stock Movement
 ↓
Inventory decreases
 ↓
Waste analytics updated
```

Example:

```text
Chicken:
2 kg

Reason:
EXPIRED
```

Creates:

```text
StockMovement:
type = WASTE
quantity = -2kg
```

Waste cost:

```text
waste_cost = wasted_quantity × unit_cost
```

---

# 16. FORECAST ENGINE

## 16.1 Philosophy

The MVP forecasting engine must be:

- simple
- deterministic
- explainable
- fast
- testable

Do not label deterministic calculations as "AI".

---

## 16.2 Inputs

Forecast may use:

```text
historical sales
historical ingredient consumption
current stock
supplier lead time
incoming purchase orders
safety stock
historical variance
```

---

## 16.3 Basic Demand Forecast

For MVP:

```text
average_daily_usage =
    total_usage / number_of_active_days
```

Example:

```text
Usage over 7 days:
70 kg

Average:
70 / 7 = 10 kg/day
```

---

## 16.4 Days Until Empty

```text
days_until_empty =
    current_stock / average_daily_usage
```

Example:

```text
current_stock = 25 kg
average_usage = 10 kg/day

days_until_empty = 2.5 days
```

If average usage is zero:

```text
days_until_empty = INFINITY / NOT_APPLICABLE
```

Do not divide by zero.

---

## 16.5 Fallback Strategy

Forecast confidence hierarchy:

```text
Enough history
    ↓
Moving average

Insufficient history
    ↓
Available historical average

No history
    ↓
Manual minimum stock / reorder threshold
```

Forecast confidence:

```text
HIGH
MEDIUM
LOW
```

Example:

```text
LOW

Only 3 days of historical usage are available.
```

Never present low-confidence predictions as certainty.

---

# 17. REORDER RECOMMENDATION ENGINE

## 17.1 Reorder Point

Basic formula:

```text
reorder_point =
    average_daily_usage × lead_time_days
    + safety_stock
```

Example:

```text
average_daily_usage = 10 kg
lead_time = 2 days
safety_stock = 5 kg

reorder_point = 25 kg
```

Trigger:

```text
current_stock <= reorder_point
```

---

## 17.2 Target Stock

```text
target_stock =
    average_daily_usage ×
    (lead_time_days + review_period_days)
    + safety_stock
```

---

## 17.3 Recommended Quantity

```text
recommended_quantity =
    target_stock
    - current_stock
    - incoming_quantity
```

Minimum:

```text
recommended_quantity >= 0
```

Never recommend negative quantities.

---

## 17.4 Purchase Unit Rounding

If supplier sells in whole units:

```text
Calculated:
2.18 kg

Supplier unit:
1 kg

Final recommendation:
3 kg
```

The algorithm must support sensible purchase-unit rounding.

---

## 17.5 Recommendation Explanation

Every recommendation should display:

```text
Current stock
Average daily usage
Lead time
Safety stock
Incoming stock
Expected depletion
Reorder point
Recommended quantity
Forecast confidence
```

Example:

```text
Tomato

Current stock:
4 kg

Average usage:
2.2 kg/day

Supplier lead time:
2 days

Expected empty:
~1.8 days

Recommendation:
Purchase 8 kg

Reason:
Stock is expected to fall below the reorder point
before the next supplier delivery window.
```

---

# 18. BUSINESS INSIGHT ENGINE

The system should convert metrics into actions.

## 18.1 Insight Types

```text
STOCKOUT_RISK
WASTE_SPIKE
SLOW_MOVING_STOCK
HIGH_USAGE
PURCHASE_DUE
SUPPLIER_DELAY
LOW_FORECAST_CONFIDENCE
```

## 18.2 Severity

```text
INFO
WARNING
CRITICAL
```

## 18.3 Insight Structure

```text
type
severity
title
description
recommended_action
entity_type
entity_id
created_at
is_read
```

Example:

```text
CRITICAL

Chicken stock predicted to run out tomorrow.

Action:
Review purchase recommendation.
```

---

# 19. DASHBOARD REQUIREMENTS

The dashboard must answer three questions.

## 19.1 What Needs Attention?

Show:

- critical stock
- reorder candidates
- waste alerts

## 19.2 What Happened?

Show:

- sales
- inventory value
- waste
- purchases

## 19.3 What Should I Do?

Show:

- purchase recommendations
- high-risk ingredients
- actionable business insights

## 19.4 Dashboard Hierarchy

```text
Critical Actions
       ↓
Inventory Health
       ↓
Business Metrics
       ↓
Trends
```

Do not bury actions below decorative charts.

---

# 20. PAGE & ROUTE SPECIFICATION

Required routes:

```text
/auth/login

/dashboard

/products
/products/new
/products/:id

/ingredients
/ingredients/new
/ingredients/:id

/recipes
/recipes/:id

/inventory
/inventory/:id
/inventory/movements

/sales
/sales/new
/sales/:id

/suppliers
/suppliers/new
/suppliers/:id

/purchase-orders
/purchase-orders/new
/purchase-orders/:id

/waste
/waste/new

/forecast
/forecast/:ingredientId

/recommendations

/insights

/settings/business
/settings/users
```

---

# 21. UX REQUIREMENTS

## 21.1 Page Contract

Every page must define:

```text
Purpose
Primary user
Primary action
Secondary actions
Data required
Loading state
Empty state
Error state
Success state
Permission rules
```

---

## 21.2 Actionable Warnings

Bad:

```text
Stock Low
```

Good:

```text
Tomato is expected to run out in 1.8 days.

[Review Recommendation]
```

---

## 21.3 Empty States

Bad:

```text
No data.
```

Good:

```text
No purchase orders yet.

Create your first purchase order to start
tracking supplier deliveries.

[Create Purchase Order]
```

---

## 21.4 Loading States

Every asynchronous operation requires an explicit state:

- skeleton
- spinner
- disabled submit
- progress indicator

Choose the appropriate pattern for the context.

---

## 21.5 Error States

Bad:

```text
500 Internal Server Error
```

Good:

```text
We couldn't save this purchase order.

Please try again.

If the problem continues, check your internet connection
or contact the administrator.
```

Never expose:

- stack traces
- SQL
- internal paths
- secrets
- tokens

---

# 22. TECHNICAL ARCHITECTURE

The exact framework may be selected by the engineering team, but the
architecture must preserve separation of concerns.

## 22.1 Frontend

Preferred conceptual architecture:

```text
UI
 ↓
Feature Layer
 ↓
Application / Query Layer
 ↓
API Client
 ↓
Backend
```

Do not put all business logic into UI components.

Bad:

```text
React Component
 ├── fetching
 ├── forecast calculation
 ├── inventory calculation
 ├── authorization
 └── rendering
```

Good:

```text
Page
 ↓
Feature Hook / Controller
 ↓
Service
 ↓
API
```

---

## 22.2 Backend

Preferred architecture:

```text
Routes
 ↓
Controller
 ↓
Application Service
 ↓
Domain Logic
 ↓
Repository
 ↓
Database
```

Example:

```text
POST /purchase-orders/:id/receive
        ↓
PurchaseOrderController
        ↓
ReceivePurchaseOrderService
        ↓
InventoryService
        ↓
StockMovementRepository
        ↓
Database
```

---

## 22.3 Domain Functions

Important business logic should be centralized.

Examples:

```text
calculateReorderPoint()
calculateRecommendedQuantity()
calculateCurrentStock()
calculateForecast()
receivePurchaseOrder()
consumeInventoryForSale()
recordWaste()
```

---

# 23. DATABASE REQUIREMENTS

## 23.1 Core Tables

Recommended tables:

```text
users
businesses
business_members
products
ingredients
recipes
recipe_items
suppliers
supplier_items
inventory
stock_movements
sales
sale_items
purchase_orders
purchase_order_items
waste_records
forecasts
reorder_recommendations
business_insights
```

---

## 23.2 Database Rules

Use:

- foreign keys
- unique constraints
- indexes
- timestamps
- soft delete where appropriate
- database transactions
- explicit tenant ownership

Avoid:

- duplicated state
- arbitrary JSON blobs
- unnecessary polymorphic relations
- nullable fields without semantic meaning

---

## 23.3 Financial Values

Store monetary values using integer smallest currency units where practical.

For IDR:

```text
Rp 15.000
```

may be represented as:

```text
15000
```

Do not use floating-point arithmetic for financial calculations.

---

## 23.4 Quantity Values

Quantities may require decimal precision.

Example:

```text
2.18 kg
```

Store enough precision for internal calculations, but display sensible
rounding.

---

# 24. API REQUIREMENTS

## 24.1 REST Convention

Example:

```http
GET    /api/products
POST   /api/products
GET    /api/products/:id
PATCH  /api/products/:id
DELETE /api/products/:id
```

Inventory:

```http
GET /api/inventory
GET /api/inventory/:ingredientId
GET /api/inventory/:ingredientId/movements
```

Forecast:

```http
GET /api/forecast
GET /api/forecast/:ingredientId
```

Recommendations:

```http
GET  /api/recommendations
POST /api/recommendations/:id/approve
POST /api/recommendations/:id/dismiss
```

Purchase orders:

```http
GET  /api/purchase-orders
POST /api/purchase-orders
GET  /api/purchase-orders/:id
POST /api/purchase-orders/:id/receive
POST /api/purchase-orders/:id/cancel
```

---

## 24.2 Response Format

Success:

```json
{
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Unable to create purchase order.",
    "details": {}
  }
}
```

Never expose internal exceptions directly.

---

# 25. AUTHENTICATION & AUTHORIZATION

Every protected operation must validate:

```text
authenticated user
+
business membership
+
role permission
```

Frontend route protection is not sufficient.

Server-side authorization is mandatory.

## Suggested Roles

```text
OWNER
MANAGER
STAFF
```

Example:

### OWNER

- all permissions

### MANAGER

- inventory
- sales
- suppliers
- purchases
- analytics

### STAFF

- sales
- stock viewing
- waste recording
- purchase receiving where permitted

---

# 26. MULTI-TENANCY

All business-owned data must be scoped by:

```text
business_id
```

Business A must never be able to access Business B data.

Every relevant query must enforce tenant isolation.

This rule applies to:

- API
- service layer
- repository
- reporting
- background jobs
- forecast calculations

---

# 27. SECURITY

Never commit:

```text
API keys
passwords
tokens
production credentials
private secrets
```

Use environment variables.

Example:

```env
DATABASE_URL=
AUTH_SECRET=
API_KEY=
```

`.env` must not be committed with real secrets.

## Security Checklist

```text
[ ] authentication works
[ ] server-side authorization
[ ] tenant isolation
[ ] input validation
[ ] SQL injection protection
[ ] XSS mitigation
[ ] CSRF strategy where applicable
[ ] secrets removed
[ ] production logs sanitized
[ ] errors sanitized
```

---

# 28. TESTING

Critical business logic must have automated tests.

Minimum coverage:

```text
inventory calculation
recipe consumption
sale stock deduction
purchase receiving
partial purchase receiving
waste deduction
forecast calculation
reorder point
recommended quantity
purchase rounding
authorization
tenant isolation
```

## 28.1 Forecast Test

Given:

```text
usage = [10, 12, 8, 10, 11, 9, 10]
```

Expected:

```text
average_daily_usage = 10
```

Given:

```text
current_stock = 15
lead_time = 2
safety_stock = 5
```

Expected:

```text
reorder_point = 25
```

Therefore:

```text
15 <= 25
```

Recommendation must be generated.

---

# 29. SEED & DEMO DATA

The development environment should contain:

```text
1 business
1 owner
2 staff
10 products
15 ingredients
10 recipes
3 suppliers
30+ sales transactions
multiple stock movements
multiple purchase orders
waste records
forecast records
reorder candidates
business insights
```

## 29.1 Demo Data Requirements

The demo environment must NOT look empty.

Prepare:

```text
healthy inventory
low inventory
critical inventory
waste records
different supplier lead times
incoming purchase orders
generated recommendations
```

---

# 30. ANALYTICS & KPI

Track:

```text
Stockout Rate
Waste Cost
Inventory Value
Inventory Turnover
Purchase Frequency
Average Supplier Lead Time
Forecast Error
Recommendation Acceptance Rate
```

## 30.1 Stockout Rate

Conceptual:

```text
stockout_rate =
    stockout_events / total_monitored_periods
```

## 30.2 Waste Cost

```text
waste_cost =
    sum(wasted_quantity × unit_cost)
```

## 30.3 Forecast Error

Basic:

```text
forecast_error =
    actual_usage - predicted_usage
```

For aggregated evaluation, MAE/MAPE may be introduced when enough
historical data exists.

Do not claim percentage improvements without actual evidence.

---

# 31. PERFORMANCE

Target:

```text
fast initial render
fast dashboard load
minimal unnecessary requests
pagination for large lists
debounced search
optimized queries
```

Do not introduce premature optimization.

Measure before adding complexity.

---

# 32. ACCESSIBILITY

Minimum requirements:

- semantic HTML
- keyboard navigation
- visible focus
- readable contrast
- accessible labels
- accessible buttons
- field-level validation
- descriptive error messages
- responsive layouts

Do not communicate state by color alone.

Bad:

```text
red = danger
green = safe
```

Good:

```text
CRITICAL
Stock expected to run out tomorrow.
```

---

# 33. GIT & DEVELOPMENT WORKFLOW

## 33.1 Branch Naming

```text
feature/inventory-dashboard
feature/forecast-engine
feature/purchase-order
fix/stock-calculation
refactor/inventory-service
docs/update-prd
test/forecast-engine
```

## 33.2 Commit Naming

```text
feat: add inventory risk summary
fix: prevent negative stock
refactor: extract inventory service
test: add forecast engine tests
docs: update database schema
```

## 33.3 Commit Rule

One commit should represent one logical change.

Bad:

```text
feat: dashboard + auth + database + forecast + styling
```

Good:

```text
feat: add inventory risk summary
```

---

# 34. AI CODING AGENT CONSTITUTION

This section is the operating contract for AI coding agents.

## 34.1 Source of Truth Priority

When making implementation decisions:

```text
1. Explicit user requirement
2. This document
3. Existing architecture documentation
4. Existing database contract
5. Existing API contract
6. Existing implementation
7. Reasonable engineering judgment
```

If two requirements conflict:

1. Identify the conflict.
2. Prefer the higher-priority source.
3. Do not silently change domain behavior.
4. Document the assumption or ask for clarification.

---

## 34.2 Before Coding

The agent MUST:

```text
1. Read this document.
2. Inspect the relevant source files.
3. Inspect related imports and dependencies.
4. Inspect database/schema if affected.
5. Inspect tests if they exist.
6. Identify affected domain boundaries.
7. Define the smallest safe change.
8. Implement.
9. Test.
10. Review.
```

---

## 34.3 Do Not Code Blindly

Before modifying a file:

- inspect the file
- inspect callers
- inspect dependencies
- inspect tests
- inspect domain constraints

Do not overwrite code simply because a new implementation looks cleaner.

Preserve existing behavior unless the requirement explicitly changes it.

---

## 34.4 Change Strategy

Prefer:

```text
small change
 ↓
test
 ↓
verify
 ↓
next change
```

Avoid:

```text
rewrite entire application
 ↓
hope it works
```

---

## 34.5 No Fake Implementation

Forbidden in final production behavior:

```text
hardcoded recommendation
fake forecast
fake AI response
fake stock balance
fake API success
TODO pretending to work
```

Mocks are allowed only for:

- tests
- isolated UI development
- explicit demo fixtures

Production logic must be real.

---

## 34.6 No Magic Numbers

Bad:

```ts
if (stock < 17) ...
```

Good:

```ts
if (stock <= reorderPoint) ...
```

If a constant is required:

```text
DEFAULT_FORECAST_DAYS
DEFAULT_SAFETY_STOCK_DAYS
DEFAULT_REVIEW_PERIOD_DAYS
```

Document its purpose.

---

## 34.7 Naming

Prefer explicit names.

Bad:

```text
data
item
result
process()
handle()
```

Good:

```text
inventoryItem
purchaseOrder
recommendedQuantity
calculateReorderPoint()
receivePurchaseOrder()
```

---

## 34.8 Comments

Comments should explain **why**, not obvious implementation details.

Bad:

```ts
// Add 1 to quantity
quantity += 1;
```

Good:

```ts
// Add safety stock to protect against normal demand variance
// during supplier lead time.
```

---

## 34.9 Domain Logic

Business rules must not be scattered across:

- UI components
- controllers
- random SQL queries
- route handlers

Centralize domain rules.

Examples:

```text
calculateReorderPoint()
calculateRecommendedQuantity()
calculateForecast()
consumeInventoryForSale()
receivePurchaseOrder()
recordWaste()
```

---

## 34.10 Transactional Operations

The following must be atomic.

### Sale Completion

```text
create sale
+
create sale items
+
create stock movements
```

### Purchase Receiving

```text
receive PO
+
update PO status
+
create stock movements
```

### Waste Recording

```text
create waste record
+
create stock movement
```

---

## 34.11 Debugging Procedure

When something breaks:

```text
1. Reproduce
2. Read the error
3. Identify the boundary
4. Inspect input data
5. Inspect network/API
6. Inspect backend logs
7. Identify root cause
8. Fix root cause
9. Add regression test
```

Never hide errors with empty catch blocks.

Bad:

```ts
try {
  ...
} catch {}
```

---

## 34.12 Agent Honesty

The agent must NEVER:

- fabricate test results
- claim an endpoint works without testing
- claim a migration succeeded without running it
- claim a feature is complete when mocked
- claim deployment succeeded without verification
- silently delete functionality

---

# 35. AGENT OPERATING PROCEDURE

## 35.1 New Feature

```text
Read requirements
 ↓
Inspect existing architecture
 ↓
Identify domain entities
 ↓
Identify API changes
 ↓
Identify DB changes
 ↓
Implement domain logic
 ↓
Implement API
 ↓
Implement UI
 ↓
Add validation
 ↓
Add authorization
 ↓
Add tests
 ↓
Test integration
 ↓
Review UX
 ↓
Update documentation
```

## 35.2 Bug Fix

```text
Reproduce
 ↓
Find root cause
 ↓
Write or update regression test
 ↓
Fix
 ↓
Run focused tests
 ↓
Run broader test suite
 ↓
Review side effects
```

---

# 36. DEFINITION OF DONE

A feature is DONE only when:

```text
[ ] requirement implemented
[ ] UI implemented
[ ] backend implemented where required
[ ] database implemented where required
[ ] validation implemented
[ ] authorization implemented
[ ] loading state implemented
[ ] empty state implemented
[ ] error state implemented
[ ] tests written for critical logic
[ ] responsive behavior checked
[ ] documentation updated
[ ] no known critical regression
```

"Works on my machine" is not Definition of Done.

---

# 37. COMPETITION ALIGNMENT

> **IMPORTANT:** Exact competition-specific requirements must be verified
> against the official guidebook before final submission. The uploaded
> guidebook was not fully machine-readable in the current file retrieval
> context, so this section deliberately does not invent unverified rules.

## 37.1 Working Alignment

The product is designed around:

```text
Business Problem
 ↓
Digital Solution
 ↓
Real Data
 ↓
Automation / Intelligence
 ↓
Actionable Recommendation
 ↓
Business Impact
```

## 37.2 Competition-Focused Product Strengths

The product should demonstrate:

### Relevance

The problem is directly related to operational challenges faced by UMKM.

### Innovation

The differentiator is not merely CRUD inventory management.

The differentiator is:

```text
Inventory Data
+
Historical Usage
+
Supplier Lead Time
+
Forecasting
+
Recommendation
```

### Functionality

The application must be a real interactive system:

```text
transaction
→ inventory mutation
→ forecast
→ recommendation
→ purchase
→ receiving
```

### Technical Quality

The system should demonstrate:

- structured architecture
- domain separation
- transactional data updates
- tenant isolation
- explainable calculations
- automated tests

### UX/UI

The interface should prioritize:

- actionable information
- simple decision making
- responsive layouts
- clear statuses
- understandable recommendations

### Business Impact

Potential impact areas:

```text
reduce stockout risk
reduce unnecessary inventory
reduce waste
reduce purchasing guesswork
improve operational visibility
```

Do not claim numerical impact without measured evidence.

---

# 38. DEMO STRATEGY

## 38.1 Demo Narrative

The demo should follow:

```text
PROBLEM
 ↓
DATA
 ↓
SYSTEM ANALYSIS
 ↓
RECOMMENDATION
 ↓
USER ACTION
 ↓
BUSINESS IMPACT
```

## 38.2 Preferred Demo Flow

```text
1. Login
2. Open dashboard
3. Show critical inventory
4. Open ingredient
5. Show usage history
6. Show forecast
7. Explain reorder recommendation
8. Approve recommendation
9. Create purchase order
10. Receive goods
11. Show inventory update
12. Show updated business insight
```

## 38.3 What NOT to Spend Demo Time On

Avoid spending most of the demo on:

- login
- settings
- CRUD
- navigation
- cosmetic animations

Spend time on the intelligent workflow.

---

# 39. RISKS

## 39.1 Scope Creep

Risk:

```text
The project becomes an ERP.
```

Mitigation:

```text
Keep the inventory → forecast → reorder loop as the core.
```

---

## 39.2 Fake Intelligence

Risk:

```text
Forecast is hardcoded or meaningless.
```

Mitigation:

```text
Use deterministic formulas.
Show calculation inputs.
Add automated tests.
```

---

## 39.3 Insufficient Data

Risk:

```text
Forecast has too little historical data.
```

Mitigation:

```text
Use confidence levels.
Use fallback rules.
Seed realistic demo data.
```

---

## 39.4 Data Inconsistency

Risk:

```text
Inventory number differs between screens.
```

Mitigation:

```text
Centralize stock calculation.
Use stock movements.
Use transactions.
Test inventory mutations.
```

---

## 39.5 Overengineering

Risk:

```text
Too much infrastructure, too little product.
```

Mitigation:

```text
Prefer a modular monolith for MVP.
Introduce complexity only when justified.
```

---

## 39.6 Demo Failure

Risk:

```text
Live demo breaks because of empty data or unstable services.
```

Mitigation:

```text
Prepare seeded demo data.
Test the full journey.
Keep a stable demo environment.
Have a backup recording if allowed by competition rules.
```

---

# 40. ROADMAP

## Phase 0 — Foundation

```text
[ ] repository setup
[ ] environment setup
[ ] database setup
[ ] authentication
[ ] base UI system
[ ] linting
[ ] testing framework
```

---

## Phase 1 — Core Data

```text
[ ] business
[ ] products
[ ] ingredients
[ ] recipes
[ ] suppliers
```

---

## Phase 2 — Transactions

```text
[ ] sales
[ ] recipe-based consumption
[ ] inventory
[ ] stock movements
[ ] waste
```

---

## Phase 3 — Purchasing

```text
[ ] purchase orders
[ ] purchase lifecycle
[ ] receiving
[ ] supplier items
```

---

## Phase 4 — Intelligence

```text
[ ] usage aggregation
[ ] forecast engine
[ ] reorder point
[ ] recommended quantity
[ ] confidence
[ ] business insights
```

---

## Phase 5 — Dashboard

```text
[ ] inventory health
[ ] critical actions
[ ] recommendations
[ ] waste analytics
[ ] business metrics
```

---

## Phase 6 — Hardening

```text
[ ] authorization audit
[ ] tenant isolation audit
[ ] critical tests
[ ] responsive QA
[ ] error states
[ ] loading states
[ ] empty states
[ ] performance review
```

---

## Phase 7 — Competition Preparation

```text
[ ] seed demo data
[ ] polish UX
[ ] validate product narrative
[ ] validate business impact claims
[ ] prepare demo flow
[ ] prepare screenshots
[ ] prepare documentation
[ ] verify competition requirements
```

---

# 41. FINAL RELEASE GATE

## Product

```text
[ ] Core user journey works end-to-end
[ ] Dashboard is actionable
[ ] Recommendations are explainable
[ ] Inventory reflects actual transactions
[ ] Purchase receiving updates inventory
[ ] Waste affects inventory
```

## Engineering

```text
[ ] Build succeeds
[ ] Tests pass
[ ] Migrations work
[ ] API errors handled
[ ] Authorization works
[ ] Tenant isolation verified
[ ] No production secrets committed
```

## UX

```text
[ ] Desktop works
[ ] Tablet works
[ ] Mobile works
[ ] Loading states exist
[ ] Empty states exist
[ ] Error states exist
[ ] Forms are validated
[ ] Critical actions are obvious
```

## Demo

```text
[ ] Demo data seeded
[ ] No broken routes
[ ] No fake production behavior
[ ] Forecast can be demonstrated
[ ] Recommendation can be demonstrated
[ ] Purchase workflow can be demonstrated
[ ] Inventory update can be demonstrated
[ ] Business insight can be demonstrated
```

## Competition

```text
[ ] Official guidebook re-verified
[ ] Submission requirements verified
[ ] Technical requirements verified
[ ] Demo requirements verified
[ ] Proposal requirements verified
[ ] Repository requirements verified
[ ] Deadline verified
```

---

# GOLDEN RULE

> Build the smallest real system that solves the core problem.
>
> Make its intelligence explainable.
>
> Make its data trustworthy.
>
> Make its business value obvious within five minutes.

---

# APPENDIX A — CORE BUSINESS FLOW

```text
                   ┌──────────────────┐
                   │      SALES       │
                   └────────┬─────────┘
                            │
                            ▼
                   ┌──────────────────┐
                   │ RECIPE / BOM     │
                   └────────┬─────────┘
                            │
                            ▼
                   ┌──────────────────┐
                   │ STOCK CONSUMPTION│
                   └────────┬─────────┘
                            │
                            ▼
                   ┌──────────────────┐
                   │    INVENTORY     │
                   └────────┬─────────┘
                            │
                  ┌─────────┴─────────┐
                  ▼                   ▼
          ┌──────────────┐    ┌──────────────┐
          │   FORECAST   │    │ WASTE TRACK  │
          └──────┬───────┘    └──────┬───────┘
                 │                   │
                 └─────────┬─────────┘
                           ▼
                  ┌──────────────────┐
                  │ RISK DETECTION   │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │ REORDER ENGINE   │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │ PURCHASE ORDER   │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │ RECEIVE GOODS    │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │ INVENTORY UPDATE │
                  └──────────────────┘
```

---

# APPENDIX B — EXAMPLE END-TO-END SCENARIO

Business:

```text
Warung Makan Budi
```

Ingredient:

```text
Tomato
Current stock: 4 kg
Average daily usage: 2.2 kg
Supplier lead time: 2 days
Safety stock: 3 kg
Incoming stock: 0 kg
```

Calculation:

```text
reorder_point
= 2.2 × 2 + 3
= 7.4 kg
```

Since:

```text
4 kg <= 7.4 kg
```

the ingredient becomes a reorder candidate.

Expected depletion:

```text
4 / 2.2
≈ 1.82 days
```

The system generates:

```text
CRITICAL

Tomato is expected to run out in approximately 1.8 days.

Recommended action:
Create a purchase order.
```

If the target stock calculation produces:

```text
target_stock = 12 kg
```

then:

```text
recommended_quantity
= 12 - 4 - 0
= 8 kg
```

Recommendation:

```text
Purchase 8 kg tomato.
```

The user can then:

```text
Review
 ↓
Approve
 ↓
Create PO
 ↓
Order
 ↓
Receive
 ↓
Inventory increases
```

This is the core product demonstration.

---

# APPENDIX C — AGENT CHECKLIST

Before modifying code:

```text
[ ] Read this document
[ ] Understand requested feature
[ ] Locate relevant files
[ ] Inspect existing implementation
[ ] Identify domain impact
[ ] Identify DB impact
[ ] Identify API impact
[ ] Identify UX impact
```

During implementation:

```text
[ ] Keep scope focused
[ ] Preserve domain invariants
[ ] Validate inputs
[ ] Enforce authorization
[ ] Handle loading state
[ ] Handle error state
[ ] Handle empty state
[ ] Write tests
```

Before reporting completion:

```text
[ ] Run tests
[ ] Run lint
[ ] Run build
[ ] Review changed files
[ ] Check for secrets
[ ] Check for unintended behavior
[ ] Report exact result
```

---

# APPENDIX D — AGENT COMPLETION REPORT

When a coding task is complete, report:

```md
## Summary

- What was implemented

## Files Changed

- `path/to/file.ts`
- `path/to/file.tsx`

## Behavior

- What changed from the user's perspective

## Tests

- Command:
- Result:

## Database

- Migration required: Yes/No
- Migration status:

## Risks / Notes

- Known limitations
- Assumptions
- Follow-up work
```

Never report a test as passing unless it was actually executed.

---

# DOCUMENT STATUS

```text
Product specification: WORKING
Engineering constitution: ACTIVE
Competition-specific requirements: VERIFY_FROM_GUIDEBOOK
Forecast model: MVP / deterministic
Architecture: Modular monolith recommended
Primary domain: Inventory + Forecast + Purchasing
```
