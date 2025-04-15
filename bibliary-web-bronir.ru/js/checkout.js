let stripe = null;
let card = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    initializeStripe();

    document.getElementById('payment').addEventListener('change', handlePaymentMethodChange);
    document.getElementById('checkoutForm').addEventListener('submit', handleSubmit);
});

function loadCart() {
    fetch('get_cart.php')
        .then(response => response.json())
        .then(cart => {
            updateCartDisplay(cart);
        })
        .catch(error => console.error('Error:', error));
}

function updateCartDisplay(cart) {
    const cartItemsContainer = document.querySelector('.cart-items');
    cartItemsContainer.innerHTML = '';

    let subtotal = 0;

    cart.items.forEach(item => {
        const itemElement = createCartItemElement(item);
        cartItemsContainer.appendChild(itemElement);
        subtotal += item.price * item.quantity;
    });

    document.getElementById('subtotal').textContent = `${subtotal} ₽`;
    
    const delivery = 500; // Фиксированная стоимость доставки
    document.getElementById('delivery').textContent = `${delivery} ₽`;

    const total = subtotal + delivery;
    document.getElementById('total').textContent = `${total} ₽`;
}




function createCartItemElement(item) {
  const itemElement = document.createElement('div');
  itemElement.className = 'cart-item';
  itemElement.innerHTML = `
      <div class="item-image">
          <img src="images/${item.image}" alt="${item.name}">
      </div>
      <div class="item-info">
          <h3>${item.name}</h3>
          <p>Количество: 
              <button class="quantity-btn" data-action="decrease" data-id="${item.id}">-</button>
              <span class="item-quantity">${item.quantity}</span>
              <button class="quantity-btn" data-action="increase" data-id="${item.id}">+</button>
          </p>
          <p class="price">${item.price * item.quantity} ₽</p>
      </div>
      <button class="remove-item" data-id="${item.id}">Удалить</button>
  `;

  itemElement.querySelector('.remove-item').addEventListener('click', () => removeFromCart(item.id));
  itemElement.querySelectorAll('.quantity-btn').forEach(btn => {
      btn.addEventListener('click', () => updateQuantity(item.id, btn.dataset.action));
  });

  return itemElement;
}

function removeFromCart(itemId) {
  fetch('update_cart.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({ action: 'remove', id: itemId }),
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          loadCart();
      } else {
          alert('Произошла ошибка при удалении товара из корзины.');
      }
  })
  .catch(error => console.error('Error:', error));
}

function updateQuantity(itemId, action) {
  fetch('update_cart.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({ action: action, id: itemId }),
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          loadCart();
      } else {
          alert('Произошла ошибка при обновлении количества товара.');
      }
  })
  .catch(error => console.error('Error:', error));
}

function initializeStripe() {
  stripe = Stripe('your_publishable_key'); // Замените на ваш ключ Stripe
  const elements = stripe.elements();
  card = elements.create('card');
  card.mount('#card-element');
}

function handlePaymentMethodChange(e) {
  const cardElement = document.getElementById('card-element');
  if (e.target.value === 'card') {
      cardElement.classList.remove('hidden');
  } else {
      cardElement.classList.add('hidden');
  }
}

async function handleSubmit(event) {
  event.preventDefault();
  
  const button = document.querySelector('.submit-button');
  const form = event.target;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData);
  
  if (!validateForm(data)) {
      return false;
  }
  
  button.disabled = true;
  button.textContent = 'Обработка заказа...';
  
  try {
      if (data.payment === 'card') {
          const {token, error} = await stripe.createToken(card);
          if (error) {
              throw new Error(error.message);
          }
          data.stripeToken = token.id;
      }
      
      const response = await fetch('process_order.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
      });

      const result = await response.json();
      
      if (result.success) {
          showSuccess(result.orderId);
          form.reset();
      } else {
          throw new Error(result.message);
      }
  } catch (error) {
      alert(`Ошибка: ${error.message}`);
  } finally {
      button.disabled = false;
      button.textContent = 'Оформить заказ';
  }
  
  return false;
}

function validateForm(data) {
  // Реализуйте валидацию формы здесь
  return true;
}

function showSuccess(orderId) {
  alert(`Спасибо за заказ! Ваш номер заказа: ${orderId}`);
  // Здесь можно добавить код для перенаправления на страницу подтверждения заказа
}
