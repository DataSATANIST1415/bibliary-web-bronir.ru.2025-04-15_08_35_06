document.addEventListener('DOMContentLoaded', function() {
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    const removeButtons = document.querySelectorAll('.remove-btn');
    const clearCartButton = document.getElementById('clear-cart');

    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const cartId = this.dataset.cartId;
            updateQuantity(cartId, action);
        });
    });

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.dataset.cartId;
            removeItem(cartId);
        });
    });

    if (clearCartButton) {
        clearCartButton.addEventListener('click', clearCart);
    }

    function updateQuantity(cartId, action) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cartId: cartId, action: action }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Произошла ошибка при обновлении количества товара.');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }

    function removeItem(cartId) {
        if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cartId: cartId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при удалении товара из корзины.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    }

    function clearCart() {
        if (confirm('Вы уверены, что хотите очистить всю корзину?')) {
            fetch('clear_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при очистке корзины.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    }
});
