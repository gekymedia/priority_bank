# API Key System Linking Update

## Summary

Updated the API key creation process to link tokens with external systems and configure callback URLs.

## Changes Made

### 1. Enhanced API Key Creation Form

**Added Fields:**
- **System Selection:** Dropdown to select which external system this API key is for
- **Callback URL:** Field to set/update the webhook callback URL for the selected system

### 2. Updated Controller (`ApiKeyController.php`)

**New Validation:**
```php
'system_id' => 'nullable|exists:systems_registry,system_id',
'callback_url' => 'nullable|url|max:255',
```

**New Functionality:**
- When a system is selected, the callback URL can be set/updated
- Token ID is stored in the system's metadata for tracking
- Links tokens to systems for better management

### 3. Enhanced View Display

**New Features:**
- Shows which system each token is linked to
- Displays callback URL for linked systems
- Better organization of token information

## How It Works Now

### Creating an API Key for External System:

1. **Go to API Keys page** → Click "Create New API Key"
2. **Enter Key Name:** e.g., "Gekymedia System Integration"
3. **Select External System (Optional):** Choose from dropdown (gekymedia, priority_admissions, priority_accommodation, etc.)
4. **Enter Callback URL (Optional):** Webhook URL where Priority Bank will send data back
   - If system already has a callback URL, it will be pre-filled
   - You can update it if needed
5. **Create:** Token is created and system is updated with callback URL

### What Happens:

- ✅ Token is created (Laravel Sanctum Personal Access Token)
- ✅ If system selected: System's `callback_url` is updated in `systems_registry` table
- ✅ Token ID is stored in system's `metadata` for tracking
- ✅ Token is linked to the system for easy management

### Viewing API Keys:

- Each token now shows:
  - Token name and ID
  - Linked system name and system_id (if linked)
  - Callback URL (if set)
  - Creation date, last used, expiration
  - Permissions (if set)

## Benefits

1. **Better Organization:** Tokens are now linked to specific systems
2. **Callback URL Management:** Set callback URLs when creating tokens
3. **Visibility:** See which tokens belong to which systems
4. **One-Step Setup:** Configure both token and webhook URL in one go

## Backward Compatibility

- ✅ Existing tokens still work (no breaking changes)
- ✅ Creating tokens without system selection still works
- ✅ System linking is optional
- ✅ Callback URL can be updated later via systems registry

## Example Usage

### For Gekymedia Integration:

1. Create API Key:
   - Name: "Gekymedia Production API"
   - System: Select "Gekymedia (gekymedia)"
   - Callback URL: `https://gekymedia.com`
   - Click Create

2. Copy the token shown (e.g., `1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx`)

3. Add to Gekymedia's `.env`:
   ```env
   PRIORITY_BANK_API_TOKEN=1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

4. Priority Bank can now:
   - ✅ Receive data from Gekymedia (using the token)
   - ✅ Send data back to Gekymedia (using the callback URL)

## Technical Details

### Database Changes:
- No schema changes needed
- Uses existing `systems_registry.metadata` JSON field
- Stores `api_token_id` in metadata: `{"api_token_id": 123}`

### Token Storage:
- Tokens still stored in `personal_access_tokens` table
- System link stored in `systems_registry.metadata`
- Creates a reverse lookup relationship
