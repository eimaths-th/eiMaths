/** Keep one dashboard generation hidden until every request and chart has settled. */
export function CreateDashboardLoader({ getTasks, onStateChange, onError = () => {} }) {
    let activeLoad = null;

    function load() {
        if (activeLoad) {
            return activeLoad;
        }

        onStateChange('loading');
        activeLoad = Promise.resolve()
            .then(() => getTasks())
            .then(tasks => Promise.allSettled(tasks.map(task => Promise.resolve().then(task))))
            .then(results => {
                // Wait for the remaining tasks even after a failure so a retry cannot
                // be overwritten by a late response from the previous load.
                const failure = results.find(result => result.status === 'rejected');
                if (failure) {
                    throw failure.reason;
                }

                onStateChange('ready');
                return true;
            })
            .catch(error => {
                onStateChange('error');
                onError(error);
                return false;
            })
            .finally(() => {
                activeLoad = null;
            });

        return activeLoad;
    }

    return { load, isLoading: () => activeLoad !== null };
}
