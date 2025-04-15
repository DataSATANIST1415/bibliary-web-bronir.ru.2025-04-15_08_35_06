document.addEventListener('DOMContentLoaded', function() {
    // Load products table
    loadProductsTable();

    // Add event listeners for forms
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        editProduct();
    });

    document.getElementById('productPageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProduct();
    });

    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProductModal();
    });

    function loadProductsTable() {
    $.ajax({
        type: 'GET',
        url: 'api.php',
        data: { action: 'getProducts' },
        success: function(data) {
            const products = JSON.parse(data);
            const tableBody = document.getElementById('productsTableBody');
            tableBody.innerHTML = '';

            products.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.product_id}</td>
                    <td>${product.name}</td>
                    <td>${product.description}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editProduct(${product.product_id})">Редактировать</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.product_id})">Удалить</button>
                        <a href="#" onclick="viewProductPage(${product.product_id})">Просмотр страницы</a>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
    });
}

// Function to view product page
function viewProductPage(productId) {
    $.ajax({
        type: 'GET',
        url: 'api.php',
        data: { action: 'getProduct', productId: productId },
        success: function(data) {
            const product = JSON.parse(data);
            // Display the product page details here
            alert(`Product Page: ${product.page_name} - ${product.page_description}`);
            // You can create a modal or a new page to display the details
        }
    });
}


    // Function to create new product section
    function createNewProductSection() {
        const modal = document.getElementById('productModal');
        modal.style.display = 'block';
        document.getElementById('modalTitle').innerText = 'Добавить товар';
        resetModalForm();
    }

    // Function to reset modal form
    function resetModalForm() {
        document.getElementById('catalogPageProductNameModal').value = '';
        document.getElementById('catalogProductIdModal').value = '';
        document.getElementById('catalogProductPhotoModal').value = '';
        document.getElementById('catalogProductNameModal').value = '';
        document.getElementById('catalogProductDescriptionModal').value = '';
        document.getElementById('pageProductNameModal').value = '';
        document.getElementById('mainPhotoModal').value = '';
        document.getElementById('additionalPhotosModal').value = '';
        document.getElementById('pageProductDescriptionModal').value = '';
        document.getElementById('featuresListModal').innerHTML = '';
        document.getElementById('specsTableModal').querySelector('tbody').innerHTML = '';
    }

    // Function to add feature
    function addFeature() {
        const featureInput = document.getElementById('featureInput');
        const featuresList = document.getElementById('featuresList');
        const feature = featureInput.value.trim();
        if (feature) {
            const li = document.createElement('li');
            li.innerText = feature;
            featuresList.appendChild(li);
            featureInput.value = '';
        }
    }

    function addFeatureModal() {
        const featureInputModal = document.getElementById('featureInputModal');
        const featuresListModal = document.getElementById('featuresListModal');
        const feature = featureInputModal.value.trim();
        if (feature) {
            const li = document.createElement('li');
            li.innerText = feature;
            featuresListModal.appendChild(li);
            featureInputModal.value = '';
        }
    }

    // Function to add specification row
    function addSpecRow() {
        const specsTable = document.getElementById('specsTable');
        const tbody = specsTable.querySelector('tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" class="form-control" placeholder="Параметр"></td>
            <td><input type="text" class="form-control" placeholder="Значение"></td>
            <td><button class="btn btn-sm btn-danger" onclick="this.parentNode.parentNode.remove()">Удалить</button></td>
        `;
        tbody.appendChild(row);
    }

    function addSpecRowModal() {
        const specsTableModal = document.getElementById('specsTableModal');
        const tbody = specsTableModal.querySelector('tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" class="form-control" placeholder="Параметр"></td>
            <td><input type="text" class="form-control" placeholder="Значение"></td>
            <td><button class="btn btn-sm btn-danger" onclick="this.parentNode.parentNode.remove()">Удалить</button></td>
        `;
        tbody.appendChild(row);
    }

    // Function to edit product
    function editProduct(productId) {
        $.ajax({
            type: 'GET',
            url: 'api.php',
            data: { action: 'getProduct', productId: productId },
            success: function(data) {
                const product = JSON.parse(data);
                document.getElementById('catalogProductId').value = product.product_id;
                document.getElementById('catalogPageProductName').value = product.catalog_page_name;
                document.getElementById('catalogProductName').value = product.name;
                document.getElementById('catalogProductDescription').value = product.description;
                document.getElementById('pageProductName').value = product.page_name;
                document.getElementById('pageProductDescription').value = product.page_description;

                // Load features
                const featuresList = document.getElementById('featuresList');
                featuresList.innerHTML = '';
                $.ajax({
                    type: 'GET',
                    url: 'api.php',
                    data: { action: 'getFeatures', productId: productId },
                    success: function(data) {
                        const features = JSON.parse(data);
                        features.forEach(feature => {
                            const li = document.createElement('li');
                            li.innerText = feature.feature;
                            featuresList.appendChild(li);
                        });
                    }
                });

                // Load specifications
                const specsTable = document.getElementById('specsTable');
                const tbody = specsTable.querySelector('tbody');
                tbody.innerHTML = '';
                $.ajax({
                    type: 'GET',
                    url: 'api.php',
                    data: { action: 'getSpecs', productId: productId },
                    success: function(data) {
                        const specs = JSON.parse(data);
                        specs.forEach(spec => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td><input type="text" class="form-control" value="${spec.parameter}"></td>
                                <td><input type="text" class="form-control" value="${spec.value}"></td>
                                <td><button class="btn btn-sm btn-danger" onclick="this.parentNode.parentNode.remove()">Удалить</button></td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                });
            }
        });
    }

    // Function to save product
    function saveProduct() {
        const data = {
            product_id: document.getElementById('catalogProductId').value,
            catalog_page_name: document.getElementById('catalogPageProductName').value,
            name: document.getElementById('catalogProductName').value,
            description: document.getElementById('catalogProductDescription').value,
            page_name: document.getElementById('pageProductName').value,
            page_description: document.getElementById('pageProductDescription').value,
            main_photo: document.getElementById('mainPhoto').value,
            additional_photos: Array.from(document.getElementById('additionalPhotos').files).map(file => file.name),
            features: Array.from(document.getElementById('featuresList').children).map(li => li.innerText),
            specs: Array.from(document.getElementById('specsTable').querySelectorAll('tbody tr')).map(row => {
                const cells = row.querySelectorAll('td');
                return { parameter: cells.querySelector('input').value, value: cells[1].querySelector('input').value };
            })
        };

        $.ajax({
            type: 'POST',
            url: 'api.php',
            data: { action: 'updateProduct', data: JSON.stringify(data) },
            success: function() {
                loadProductsTable();
                resetProductPage();
            }
        });
    }

    // Function to save product from modal
    function saveProductModal() {
        const data = {
            catalog_page_name: document.getElementById('catalogPageProductNameModal').value,
            name: document.getElementById('catalogProductNameModal').value,
            description: document.getElementById('catalogProductDescriptionModal').value,
            page_name: document.getElementById('pageProductNameModal').value,
            page_description: document.getElementById('pageProductDescriptionModal').value,
            main_photo: document.getElementById('mainPhotoModal').value,
            additional_photos: Array.from(document.getElementById('additionalPhotosModal').files).map(file => file.name),
            features: Array.from(document.getElementById('featuresListModal').children).map(li => li.innerText),
            specs: Array.from(document.getElementById('specsTableModal').querySelectorAll('tbody tr')).map(row => {
                const cells = row.querySelectorAll('td');
                return { parameter: cells.querySelector('input').value, value: cells[1].querySelector('input').value };
            })
        };

        $.ajax({
            type: 'POST',
            url: 'api.php',
            data: { action: 'addProduct', data: JSON.stringify(data) },
            success: function() {
                loadProductsTable();
                resetModalForm();
                document.getElementById('productModal').style.display = 'none';
            }
        });
    }

    // Function to reset product page form
    function resetProductPage() {
        document.getElementById('catalogProductId').value = '';
        document.getElementById('catalogPageProductName').value = '';
        document.getElementById('catalogProductName').value = '';
        document.getElementById('catalogProductDescription').value = '';
        document.getElementById('pageProductName').value = '';
        document.getElementById('pageProductDescription').value = '';
        document.getElementById('featuresList').innerHTML = '';
        document.getElementById('specsTable').querySelector('tbody').innerHTML = '';
    }

    // Function to delete product
    function deleteProduct(productId) {
        $.ajax({
            type: 'POST',
            url: 'api.php',
            data: { action: 'deleteProduct', productId: productId },
            success: function() {
                loadProductsTable();
            }
        });
    }
});

// Additional functions to load features and specs when editing a product
function loadFeatures(productId, featuresList) {
    $.ajax({
        type: 'GET',
        url: 'api.php',
        data: { action: 'getFeatures', productId: productId },
        success: function(data) {
            const features = JSON.parse(data);
            featuresList.innerHTML = '';
            features.forEach(feature => {
                const li = document.createElement('li');
                li.innerText = feature.feature;
                featuresList.appendChild(li);
            });
        }
    });
}

function loadSpecs(productId, specsTable) {
    $.ajax({
        type: 'GET',
        url: 'api.php',
        data: { action: 'getSpecs', productId: productId },
        success: function(data) {
            const specs = JSON.parse(data);
            const tbody = specsTable.querySelector('tbody');
            tbody.innerHTML = '';
            specs.forEach(spec => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="text" class="form-control" value="${spec.parameter}"></td>
                    <td><input type="text" class="form-control" value="${spec.value}"></td>
                    <td><button class="btn btn-sm btn-danger" onclick="this.parentNode.parentNode.remove()">Удалить</button></td>
                `;
                tbody.appendChild(row);
            });
        }
    });
}

// Update the editProduct function to load features and specs
function editProduct(productId) {
    $.ajax({
        type: 'GET',
        url: 'api.php',
        data: { action: 'getProduct', productId: productId },
        success: function(data) {
            const product = JSON.parse(data);
            document.getElementById('catalogProductId').value = product.product_id;
            document.getElementById('catalogPageProductName').value = product.catalog_page_name;
            document.getElementById('catalogProductName').value = product.name;
            document.getElementById('catalogProductDescription').value = product.description;
            document.getElementById('pageProductName').value = product.page_name;
            document.getElementById('pageProductDescription').value = product.page_description;

            // Load features
            const featuresList = document.getElementById('featuresList');
            loadFeatures(productId, featuresList);

            // Load specifications
            const specsTable = document.getElementById('specsTable');
            loadSpecs(productId, specsTable);
        }
    });
}
