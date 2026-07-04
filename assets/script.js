document.addEventListener('DOMContentLoaded', () => {
    // Example: Simple toggle for the promotion switch
    const promotionToggle = document.getElementById('promotionToggle');
    if (promotionToggle) {
        promotionToggle.addEventListener('change', (event) => {
            if (event.target.checked) {
                console.log("Promotion enabled!");
                // Here you would typically send an API request to enable the promotion
            } else {
                console.log("Promotion disabled!");
                // Here you would typically send an API request to disable the promotion
            }
        });
    }

    // Example: Discount input buttons
    document.querySelectorAll('.discount-input button').forEach(button => {
        button.addEventListener('click', (event) => {
            const input = event.target.parentNode.querySelector('input[type="number"]');
            let currentValue = parseInt(input.value);

            if (event.target.textContent === '+') {
                input.value = currentValue + 1;
            } else if (event.target.textContent === '-') {
                if (currentValue > 0) { // Prevent going below zero
                    input.value = currentValue - 1;
                }
            }
            // In a real app, you'd trigger an update or calculate new values here
            console.log("Discount value changed to:", input.value);
        });
    });

    // You would add more JavaScript here for:
    // - Dynamic data loading (fetching data from your backend API)
    // - Chart rendering (using a library like Chart.js)
    // - More complex form submissions
    // - User interaction feedback (e.g., success messages)
    // - Handling clicks on sidebar/top nav items
});