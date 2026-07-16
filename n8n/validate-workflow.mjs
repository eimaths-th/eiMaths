import { readFileSync } from 'node:fs';

const workflowPath = new URL('./eimaths-new-customer-line-oa-lead-registration-marketing-notify.json', import.meta.url);
const rawWorkflow = readFileSync(workflowPath, 'utf8');

if (rawWorkflow.includes('test-web.eimaths-th.com')) {
  throw new Error('Workflow still references the test CRM host.');
}

const workflow = JSON.parse(rawWorkflow);
const nodeNames = new Set();
const nodeIds = new Set();

for (const node of workflow.nodes) {
  if (nodeNames.has(node.name)) {
    throw new Error(`Duplicate node name: ${node.name}`);
  }

  if (nodeIds.has(node.id)) {
    throw new Error(`Duplicate node id: ${node.id}`);
  }

  nodeNames.add(node.name);
  nodeIds.add(node.id);

  if (node.parameters?.jsCode) {
    new Function(node.parameters.jsCode);
  }
}

const nodeByName = (name) => workflow.nodes.find((node) => node.name === name);
const crmTargetGuard = new Function('$json', '$input', nodeByName('Enforce Production CRM Target').parameters.jsCode);
let blockedTestTarget = false;

try {
  crmTargetGuard(
    { config: { crmBaseUrl: 'https://test-web.eimaths-th.com' } },
    { item: { json: {} } },
  );
} catch (error) {
  blockedTestTarget = String(error.message).includes('Blocked non-production CRM target');
}

if (!blockedTestTarget) {
  throw new Error('Production CRM guard did not block the test host.');
}

const productionItem = { json: { config: { crmBaseUrl: 'https://app.eimaths-th.com/' } } };
if (crmTargetGuard(productionItem.json, { item: productionItem }) !== productionItem) {
  throw new Error('Production CRM guard did not pass through the production item.');
}

const notificationUrlGuard = new Function('$json', nodeByName('Enforce Production CRM URL').parameters.jsCode);
const guardedNotification = notificationUrlGuard({
  notificationText: 'ดูรายละเอียด: https://test-web.eimaths-th.com/admin/crm/leads/37',
});

if (guardedNotification.json.notificationText !== 'ดูรายละเอียด: https://app.eimaths-th.com/admin/crm/leads/37') {
  throw new Error('Notification URL guard did not rewrite the CRM link to production.');
}

for (const [sourceName, outputTypes] of Object.entries(workflow.connections)) {
  if (!nodeNames.has(sourceName)) {
    throw new Error(`Connection references a missing source node: ${sourceName}`);
  }

  for (const outputs of Object.values(outputTypes)) {
    for (const output of outputs) {
      for (const connection of output) {
        if (!nodeNames.has(connection.node)) {
          throw new Error(`Connection references a missing target node: ${connection.node}`);
        }
      }
    }
  }
}

console.log(`Workflow validation passed (${workflow.nodes.length} nodes).`);
