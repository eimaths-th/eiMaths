import test from 'node:test';
import assert from 'node:assert/strict';
import { CreateBookShipmentPoller } from '../../public/js/book-shipment-alert-poller.js';

test('QueuesAnImmediateRefreshWhenARequestIsAlreadyInFlight', async () => {
    let resolve_first_request;
    let request_count = 0;
    const first_response = new Promise((resolve) => {
        resolve_first_request = resolve;
    });
    const poller = CreateBookShipmentPoller({
        FetchSummary: async () => {
            request_count += 1;
            if (request_count === 1) {
                return first_response;
            }

            return { data: { pending_count: 0 } };
        },
        HandleSummary: async () => {},
        IsVisible: () => true,
        ScheduleTimeout: () => 1,
        ClearScheduledTimeout: () => {},
        AddVisibilityListener: () => {},
        RemoveVisibilityListener: () => {},
    });

    const first_poll = poller.Poll();
    poller.RequestRefresh();
    resolve_first_request({ data: { pending_count: 1 } });
    await first_poll;
    await new Promise((resolve) => setTimeout(resolve, 0));

    assert.equal(request_count, 2);
});
