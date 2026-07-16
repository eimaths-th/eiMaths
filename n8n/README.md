# eiMaths New Customer LINE OA workflow

Import `eimaths-new-customer-line-oa-lead-registration-marketing-notify.json` into n8n and configure these
environment variables on the n8n server. This workflow belongs to the dedicated
New Customer LINE OA and starts registration immediately after a follow event or
the first text message:

```text
NEW_CUSTOMER_LINE_CHANNEL_ACCESS_TOKEN=New Customer LINE OA Messaging API token
LINE_MARKETING_CHANNEL_ACCESS_TOKEN=token used by the bot in the marketing LINE group
LINE_MARKETING_GROUP_ID=LINE group ID that receives new lead alerts
EIMATHS_CRM_BASE_URL=https://app.eimaths-th.com
CRM_INTEGRATION_TOKEN=shared secret used by the CRM API
```

Set the LINE Messaging API webhook URL to the production webhook URL shown by
the `LINE Webhook` node after the workflow is activated.

## Production publish checklist

The JSON file in this directory is only the repository copy. After changing it,
import or update the workflow in n8n and publish the new active version. A saved
draft does not replace the version currently receiving LINE webhook traffic.

Before publishing, verify all of the following in the n8n canvas:

1. `Normalize LINE Event` sets `crmBaseUrl` to `https://app.eimaths-th.com`.
2. `Enforce Production CRM Target` runs before `Get LINE Profile` and blocks any
   CRM base URL other than `https://app.eimaths-th.com`.
3. `Create or Merge CRM Lead` calls
   `https://app.eimaths-th.com/api/n8n/marketing/leads` and has three retries.
4. `Enforce Production CRM URL` runs before `Notify Marketing Team`, so the
   detail link always starts with `https://app.eimaths-th.com/admin/crm/leads`.
5. Search the active workflow JSON for `test-web.eimaths-th.com`; it must return
   no matches.

Do not activate a fresh import until the real LINE marketing group ID and the
required tokens/credentials have been configured on that n8n instance.

Run `node n8n/validate-workflow.mjs` before importing to check JSON, Code node
syntax, connection targets, duplicate IDs/names, and accidental test-host URLs.

No start keyword is required. Supported restart keywords are `เริ่มใหม่`,
`ลงทะเบียนใหม่`, `แก้ข้อมูล`, and `กรอกใหม่`. After registration completes,
the conversation is handed over to staff and n8n remains silent unless the
customer explicitly sends a restart keyword.

`LINE_MARKETING_CHANNEL_ACCESS_TOKEN` may use the same value as
`NEW_CUSTOMER_LINE_CHANNEL_ACCESS_TOKEN` when the New Customer OA bot is already
a member of the marketing group. Keeping separate variables allows the two
Messaging API channels to be managed independently.

Before production use, validate the `X-Line-Signature` header at the reverse
proxy or add a signature-validation step using the LINE channel secret and the
untouched raw request body.

Set the same CRM secret in the backend and public website environments:

```text
# eiMaths backend
CRM_INTEGRATION_TOKEN=the-same-long-random-secret

# eimaths_th_new frontend
EIMATHS_CRM_INTEGRATION_TOKEN=the-same-long-random-secret
LINE_OA_CHAT_URL=https://line.me/R/oaMessage/@new_customer_oa_id/
```

Replace `@new_customer_oa_id` with the Basic ID of the dedicated New Customer
LINE OA. The website registration button must no longer point to the main OA.

The workflow keeps incomplete conversation state in n8n workflow static data.
For a high-volume production deployment, replace this storage with n8n Data
Tables, Redis, or a database-backed conversation store.
