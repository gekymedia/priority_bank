# API Keys Fields Documentation
## Priority Bank - Laravel Sanctum Implementation

### Database Table: `personal_access_tokens`

This table stores API keys using Laravel Sanctum's Personal Access Token system.

---

## Table Fields

| Field Name | Type | Description | Notes |
|------------|------|-------------|-------|
| **id** | bigint (PK) | Primary key | Auto-incrementing ID |
| **tokenable_type** | string | Polymorphic relation type | Usually `"App\Models\User"` |
| **tokenable_id** | bigint | Foreign key to tokenable model | User ID that owns the token |
| **name** | text | Token name/identifier | User-given name (e.g., "Production API", "Development API") |
| **token** | string(64) | Hashed token value | SHA-256 hash of the plain text token (unique) |
| **abilities** | text (nullable) | Token permissions/abilities | JSON array of permissions (can be null for all permissions) |
| **last_used_at** | timestamp (nullable) | Last usage timestamp | Tracked automatically by Sanctum when token is used |
| **expires_at** | timestamp (nullable) | Token expiration date | Optional expiration (indexed for performance) |
| **created_at** | timestamp | Creation timestamp | When the token was created |
| **updated_at** | timestamp | Last update timestamp | When the token was last modified |

---

## Important Notes

### Token Storage
- **Plain Text Token**: Only available when first created via `$token->plainTextToken`
- **Hashed Token**: Stored in database in the `token` field (SHA-256 hash)
- **Security**: Plain text token is NEVER stored in the database - only shown once when created

### Token Retrieval
- Use `$user->tokens()` to get all tokens for a user
- Use `PersonalAccessToken::find($id)` to find by ID
- The `token` field in the database contains the **hashed** value, not the plain text

### Fields Used in Views
Currently displayed in `api-keys/index.blade.php`:
- ✅ `$token->name` - Token name
- ✅ `$token->created_at` - Creation date
- ✅ `$token->last_used_at` - Last usage (nullable)
- ⚠️ `$token->token` - Showing hashed token (first 20 chars) - **This shows the hash, not the actual API key**

### Fields NOT Currently Displayed (Available)
- `$token->abilities` - Token permissions/abilities
- `$token->expires_at` - Expiration date (if set)
- `$token->id` - Token ID (used for deletion)
- `$token->tokenable_id` - User ID that owns the token

---

## Controller Methods

### ApiKeyController::index()
Returns: Collection of PersonalAccessToken models
Fields accessed:
- `$token->name`
- `$token->token` (hashed value)
- `$token->created_at`
- `$token->last_used_at`
- `$token->id` (for deletion)

### ApiKeyController::store()
Creates: New PersonalAccessToken via `$user->createToken($name)`
Returns: `$token->plainTextToken` (only shown once in session flash)

### ApiKeyController::destroy()
Uses: `$token->id` to find and delete token

---

## Current Implementation Issues

1. **View shows hashed token**: Line 53 in `index.blade.php` shows `substr($token->token, 0, 20)` which displays the hash, not useful information
   - **Fix**: Show token ID or masked identifier instead

2. **Missing fields display**:
   - `expires_at` - Not shown (useful if tokens have expiration)
   - `abilities` - Not shown (useful for permission-based tokens)

3. **Token identification**: The "Token ID" display is confusing - it shows hash characters, not a meaningful ID

---

## Recommended Improvements

1. Show token ID instead of hash prefix: `Token ID: #{{ $token->id }}`
2. Display expiration date if set: `Expires: {{ $token->expires_at ? $token->expires_at->format('M d, Y') : 'Never' }}`
3. Show abilities if set: `Permissions: {{ $token->abilities ? implode(', ', json_decode($token->abilities)) : 'All' }}`
4. Add ability to set expiration when creating token
5. Add ability to set permissions/abilities when creating token
