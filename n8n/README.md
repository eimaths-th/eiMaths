# eiMaths LINE OA workflow

Import `eimaths-line-oa-lead-registration-marketing-notify.json` into n8n and configure these
environment variables on the n8n server:

```text
LINE_CHANNEL_ACCESS_TOKEN=LINE Messaging API channel access token
LINE_MARKETING_GROUP_ID=LINE group ID that receives new lead alerts
EIMATHS_CRM_BASE_URL=https://test-web.eimaths-th.com
CRM_INTEGRATION_TOKEN=shared secret used by the CRM API
```

Set the LINE Messaging API webhook URL to the production webhook URL shown by
the `LINE Webhook` node after the workflow is activated.

Before production use, validate the `X-Line-Signature` header at the reverse
proxy or add a signature-validation step using the LINE channel secret and the
untouched raw request body.

Set the same CRM secret in the backend and public website environments:

```text
# eiMaths backend
CRM_INTEGRATION_TOKEN=the-same-long-random-secret

# eimaths_th_new frontend
EIMATHS_CRM_INTEGRATION_TOKEN=the-same-long-random-secret
LINE_OA_CHAT_URL=https://line.me/R/oaMessage/@eiMaths/
```

The workflow keeps incomplete conversation state in n8n workflow static data.
For a high-volume production deployment, replace this storage with n8n Data
Tables, Redis, or a database-backed conversation store.
