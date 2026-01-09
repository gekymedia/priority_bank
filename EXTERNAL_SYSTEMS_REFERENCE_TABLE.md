# External Systems Connected to Priority Bank
## Reference Table for API Key Generation

Use this table when creating API keys in the Priority Bank frontend. Each system should have its callback URL configured for webhook functionality.

---

## Systems Reference Table

| System ID | System Name | Domain/Base URL | Callback URL | Webhook Endpoints | Status |
|-----------|-------------|-----------------|--------------|-------------------|--------|
| `gekymedia` | Gekymedia System | `https://gekymedia.com` | `https://gekymedia.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |
| `priority_admissions` | Priority Admissions System (CUG) | `https://catholicuniversityofghana.com` | `https://catholicuniversityofghana.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |
| `priority_accommodation` | Priority Accommodation | `https://priorityaccommodation.com` | `https://priorityaccommodation.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |
| `priority_agriculture` | Priority Agriculture | `https://priorityagriculture.com` | `https://priorityagriculture.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |
| `schoolsgh` | SchoolsGH | `https://schoolsgh.com` | `https://schoolsgh.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |
| `priority_solutions_agency` | Priority Solutions Agency | `https://prioritysolutionsagency.com` | `https://prioritysolutionsagency.com` | `/api/webhook/finance/income`<br>`/api/webhook/finance/expense` | ✅ Integrated |

---

## Detailed Information

### 1. Gekymedia System (`gekymedia`)
- **Full Name:** Gekymedia System
- **Type:** Hybrid (automated + manual)
- **Description:** Multi-Directorate System (Geky Dev, Geky Studios, Geky Prints, Geky Stations)
- **Domain:** `https://gekymedia.com`
- **Callback URL:** `https://gekymedia.com`
- **Full Webhook URLs:**
  - Income: `https://gekymedia.com/api/webhook/finance/income`
  - Expense: `https://gekymedia.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: Payment completions (from invoices)
  - Expenses: Purchase order completions/receipts

### 2. Priority Admissions System (`priority_admissions`)
- **Full Name:** Priority Admissions System (CUG)
- **Type:** Hybrid (automated + manual)
- **Description:** CUG form sales (online, automated), other universities form sales (manual), document requests, dues & services
- **Domain:** `https://catholicuniversityofghana.com`
- **Alternative Domain:** `https://cug.prioritysolutionsagency.com` (for webhook)
- **Callback URL:** `https://catholicuniversityofghana.com` or `https://cug.prioritysolutionsagency.com`
- **Full Webhook URLs:**
  - Income: `https://catholicuniversityofghana.com/api/webhook/finance/income`
  - Expense: `https://catholicuniversityofghana.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: IncomeRecord entries (form sales, dues, document requests)
  - Expenses: Expense entries (domain/hosting, SMS API, office supplies)

### 3. Priority Accommodation (`priority_accommodation`)
- **Full Name:** Priority Accommodation
- **Type:** Manual
- **Description:** Rent/bookings income, maintenance & utilities expenses
- **Domain:** `https://priorityaccommodation.com`
- **Callback URL:** `https://priorityaccommodation.com`
- **Full Webhook URLs:**
  - Income: `https://priorityaccommodation.com/api/webhook/finance/income`
  - Expense: `https://priorityaccommodation.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: Rent payments and Security deposits
  - Expenses: Maintenance payments

### 4. Priority Agriculture (`priority_agriculture`)
- **Full Name:** Priority Agriculture
- **Type:** Hybrid (automated + manual)
- **Description:** Poultry farm and crop farm - produce sales, feed, vet services, labor
- **Domain:** `https://priorityagriculture.com`
- **Callback URL:** `https://priorityagriculture.com`
- **Full Webhook URLs:**
  - Income: `https://priorityagriculture.com/api/webhook/finance/income`
  - Expense: `https://priorityagriculture.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: Produce sales
  - Expenses: Feed, vet services, labor

### 5. SchoolsGH (`schoolsgh`)
- **Full Name:** SchoolsGH
- **Type:** Automated
- **Description:** Independent SaaS - School term subscription payments, domain, hosting, SMS packages
- **Domain:** `https://schoolsgh.com`
- **Callback URL:** `https://schoolsgh.com`
- **Full Webhook URLs:**
  - Income: `https://schoolsgh.com/api/webhook/finance/income`
  - Expense: `https://schoolsgh.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: Subscription payments
  - Expenses: Domain, hosting, SMS packages

### 6. Priority Solutions Agency (`priority_solutions_agency`)
- **Full Name:** Priority Solutions Agency
- **Type:** Hybrid (automated + manual)
- **Description:** Includes Priority Travels, Priority Nova, University contracts (CUG, ANGUTECH)
- **Domain:** `https://prioritysolutionsagency.com`
- **Callback URL:** `https://prioritysolutionsagency.com`
- **Full Webhook URLs:**
  - Income: `https://prioritysolutionsagency.com/api/webhook/finance/income`
  - Expense: `https://prioritysolutionsagency.com/api/webhook/finance/expense`
- **What Gets Synced:**
  - Income: Various agency services
  - Expenses: Agency operational expenses

---

## Quick Reference for API Key Creation

When creating an API key in Priority Bank:

1. **Key Name Format:** `[System Name] Production API` or `[System Name] Integration`
   - Example: `Gekymedia Production API`
   - Example: `CUG Integration`

2. **Select System:** Choose from dropdown using System ID (e.g., `gekymedia`, `priority_admissions`)

3. **Callback URL:** Use the "Callback URL" column above
   - The form will pre-fill if the system already has a callback URL configured
   - If not set, enter the full base URL (e.g., `https://gekymedia.com`)

4. **Token Usage:** Copy the generated token and add to the system's `.env`:
   ```env
   PRIORITY_BANK_API_TOKEN=1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

---

## Notes

- All systems use the same webhook endpoint pattern: `/api/webhook/finance/{income|expense}`
- Callback URLs should be HTTPS (required for webhook security)
- If a domain is not yet live, use the intended production domain
- Callback URLs can be updated later via the systems registry or when creating new API keys

---

## Status Legend

- ✅ **Integrated:** Fully implemented with bidirectional sync
- ⚠️ **Partial:** Partially integrated (one-way sync only)
- ❌ **Not Integrated:** Not yet connected
