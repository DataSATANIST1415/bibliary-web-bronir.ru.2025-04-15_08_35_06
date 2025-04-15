document.addEventListener('DOMContentLoaded', function() {
    // Handle reorder buttons
    const reorderButtons = document.querySelectorAll('.reorder-button');
    
    reorderButtons.forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', handleReorder);
        }
    });
    
    function handleReorder(event) {
        const orderCard = event.target.closest('.order-card');
        const orderNumber = orderCard.querySelector('.order-number h3').textContent;
        const items = orderCard.querySelectorAll('.order-item');
        
        let orderDetails = {
            orderNumber: orderNumber,
            items: []
        };
        
        items.forEach(item => {
            orderDetails.items.push({
                name: item.querySelector('h4').textContent,
                quantity: parseInt(item.querySelector('p').textContent.match(/\d+/)[0]),
                price: item.querySelector('.item-price').textContent
            });
        });
        
        // Simulate adding items back to cart
        console.log('Repeating order:', orderDetails);
        alert('Товары добавлены в корзину!');
        
        // Here you would typically make an API call to add items to cart
        // window.location.href = '/cart.html';
    }
    
    // Add hover effects for order cards
    const orderCards = document.querySelectorAll('.order-card');
    
    orderCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
            card.style.transition = 'transform 0.3s ease';
            card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'none';
            card.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
});