# API Tokens Deployment Summary

## Date: 2026-01-09

All external systems have been configured with their Priority Bank API tokens.

---

## ✅ Tokens Deployed

| System | Token ID | Token Value | Domain | Status |
|--------|----------|-------------|--------|--------|
| **Gekymedia System** | #2 | `2\|g9XHuZZDGXOWIw8SQ9enECMz6aNwsWSKbWP8yNJKc485c79b` | gekymedia.com | ✅ Deployed |
| **Priority Admissions (CUG)** | #3 | `3\|R9jqTJD8S74teWYbZnyi8cenKUTvzmQYqzIHNP3idf55b73d` | catholicuniversityofghana.com | ✅ Deployed |
| **Priority Accommodation** | #8 | `8\|EMSifhDHKePMec11jgREWCm4I4SGVntDjBYEzGuz5b71171d` | accommodations.prioritysolutionsagency.com | ✅ Deployed |
| **Priority Agriculture** | #5 | `5\|XRLwoVBsFemegKxwOMK543s9FGdZ7gYWzNLC4Ehx20f1358e` | agribusiness.prioritysolutionsagency.com | ✅ Deployed |
| **SchoolsGH** | #6 | `6\|G25eamIcf8SkB09IAnRGuatDN7DGbgi8e0cjIVCi24758e53` | schoolsgh.com | ✅ Deployed |
| **Priority Solutions Agency** | #7 | `7\|lwbAOBkwHvfmG6jejyNM7rXH1mSYmA0imWzry99o9adbb8a8` | prioritysolutionsagency.com | ✅ Deployed |

---

## Environment Variables Added/Updated

Each project's `.env` file now contains:

```env
# Priority Bank API Configuration
PRIORITY_BANK_API_URL=https://bank.prioritysolutionsagency.com
PRIORITY_BANK_API_TOKEN=[system-specific-token]
PRIORITY_BANK_API_TIMEOUT=10
PRIORITY_BANK_API_MAX_RETRIES=3
```

---

## Server Paths

| System | Server Path |
|--------|-------------|
| gekymedia.com | `/home/gekymedia/web/gekymedia.com/public_html/.env` |
| catholicuniversityofghana.com | `/home/gekymedia/web/catholicuniversityofghana.com/public_html/.env` |
| accommodations.prioritysolutionsagency.com | `/home/gekymedia/web/accommodations.prioritysolutionsagency.com/public_html/.env` |
| agribusiness.prioritysolutionsagency.com | `/home/gekymedia/web/agribusiness.prioritysolutionsagency.com/public_html/.env` |
| schoolsgh.com | `/home/gekymedia/web/schoolsgh.com/public_html/.env` |
| prioritysolutionsagency.com | `/home/gekymedia/web/prioritysolutionsagency.com/public_html/.env` |

---

## Callback URLs Configured

All systems now have callback URLs set in Priority Bank's `systems_registry` table:

- ✅ gekymedia.com → `https://gekymedia.com`
- ✅ priority_admissions → `https://catholicuniversityofghana.com`
- ✅ priority_accommodation → `https://accomodation.prioritysolutionsagency.com`
- ✅ priority_agriculture → `https://agribusiness.prioritysolutionsagency.com`
- ✅ schoolsgh → `https://schoolsgh.com`
- ✅ priority_solutions_agency → `https://prioritysolutionsagency.com`

---

## Integration Status

All systems are now ready for bidirectional financial data synchronization:

1. ✅ **API Tokens:** Configured in each project's `.env`
2. ✅ **Callback URLs:** Set in Priority Bank systems registry
3. ✅ **Integration Code:** Already implemented in all projects
4. ✅ **Config Caches:** Cleared on all projects

---

## Next Steps

1. **Test Integration:**
   - Create a transaction in each external system
   - Verify it appears in Priority Bank
   - Verify webhooks work (create income/expense in Priority Bank, select external system)

2. **Monitor Logs:**
   - Check Priority Bank logs: `storage/logs/laravel.log`
   - Check external system logs for API sync status

3. **Verify Authentication:**
   - Test that each system can authenticate with Priority Bank API
   - Verify tokens work by checking `last_used_at` timestamps

---

## Token Security Notes

- ⚠️ **Never share these tokens publicly**
- ⚠️ **Tokens shown in this document should be kept secure**
- ⚠️ **If a token is compromised, delete it and create a new one in Priority Bank**
- ✅ **Each token is unique to its system**
- ✅ **Tokens can be revoked individually via Priority Bank API Keys page**

---

## Troubleshooting

If integration doesn't work:

1. Verify token is correct in `.env` file
2. Check Priority Bank API URL is correct
3. Verify callback URLs are set in systems registry
4. Check Laravel logs: `storage/logs/laravel.log`
5. Verify system is marked as active in `systems_registry` table
6. Test API endpoint manually:
   ```bash
   curl -X POST https://bank.prioritysolutionsagency.com/api/central-finance/income \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"system_id":"gekymedia","external_transaction_id":"test_123","amount":100,"date":"2026-01-09","channel":"bank"}'
   ```
