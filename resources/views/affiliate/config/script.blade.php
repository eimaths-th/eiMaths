<script type="text/javascript" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
</script>
<script>
    console.log('hi');
    document.getElementById('updateCommissionConfig').addEventListener('click', function(e) {
        // Collect updated values
        e.preventDefault();
        const data = {
            min_withdraw: document.getElementById('min_withdraw').value,
            balance_add_account_after_days: document.getElementById('balance_add_account_after_days').value,
            commission_amount: document.getElementById('commission_amount').value,
        };

        // Send AJAX request to update values
        fetch('{{ route('affiliate-configurations.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'),
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Configuration updated successfully!');
                } else {
                    alert('Failed to update configuration.');
                }
            })
            .catch(error => {
                console.log('Error:', error);
                alert('An error occurred. Please try again.');
            });
    });
</script>
