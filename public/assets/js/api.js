// JavaScript functions for API calls
function fetchCategories() {
    return fetch('../backend/controllers/categories.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch categories');
            }
            return data.data;
        });
}

function addCategory(formData) {
    return fetch('../backend/controllers/categories.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

function deleteCategory(id) {
    return fetch('../backend/controllers/categories.php', {
        method: 'DELETE',
        body: JSON.stringify({ id })
    })
    .then(response => response.json());
}

function fetchProducts() {
    return fetch('../backend/controllers/products.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch products');
            }
            return data.data;
        });
}

function addProduct(formData) {
    return fetch('../backend/controllers/products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

function updateProduct(formData) {
    return fetch('../backend/controllers/products.php', {
        method: 'PUT',
        body: formData
    })
    .then(response => response.json());
}

function deleteProduct(id) {
    return fetch('../backend/controllers/products.php', {
        method: 'DELETE',
        body: JSON.stringify({ id })
    })
    .then(response => response.json());
}

function upgradeSubscription(months) {
    return fetch('../backend/controllers/subscriptions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=upgrade&months=${months}&mock_payment=true`
    })
    .then(response => response.json());
}

function cancelSubscription() {
    return fetch('../backend/controllers/subscriptions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=cancel'
    })
    .then(response => response.json());
}

function updateProfile(formData) {
    return fetch('../backend/controllers/profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}