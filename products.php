<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'in_stock':
            return ['bg-soft-success text-success', 'In Stock'];
        case 'out_of_stock':
            return ['bg-soft-danger text-danger', 'Out of Stock'];
        default:
            return ['bg-soft-secondary text-secondary', ucfirst($status ?: 'Unknown')];
    }
}

?>
    <div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
        <?php include "./components/side-nav.php"; ?>

        <div class="flex-lg-1 h-screen overflow-y-lg-auto">
            <?php include "./components/top-nav.php"; ?>

            <header>
                <div class="container-fluid">
                    <div class="pt-6">
                        <div class="row align-items-center">
                            <div class="col-sm col-12">
                                <h1 class="h2 ls-tight">Products</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewProduct" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Add New Product</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid">
                    <div class="vstack gap-4">
                        <div class="card">
                            <div class="table-responsive px-10 py-10">
                                <table class="table table-hover table-nowrap" id="product">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Product Name</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($products) > 0): ?>
                                            <?php foreach ($products as $product): 
                                                [$badge, $action] = getStatusBadge($product['status'] ?? '');
                                            ?>
                                        <tr>
                                            <td>
                                                <img alt="avatar" src="<?= htmlspecialchars($product['image']) ?>" class="avatar avatar-xl rounded me-2">
                                                <a class="text-heading text-primary-hover font-semibold" href="#"><?= htmlspecialchars($product['name']) ?></a>
                                            </td>
                                            <td>₦<?= number_format($product['price'], 2) ?></td>
                                            <td><?= number_format($product['stock'], 0) ?></td>
                                            <td>
                                                <span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $action ?></span>
                                            </td>
                                            <td class="text-end">

                                                <a href="#" 
                                                    class="btn btn-sm btn-square btn-success sale-product" 
                                                    data-id="<?= $product['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($product['name']) ?>" 
                                                    data-price="<?= htmlspecialchars($product['price']) ?>" 
                                                    data-stock="<?= htmlspecialchars($product['stock']) ?>"
                                                    data-image="<?= htmlspecialchars($product['image']) ?>"
                                                    data-bs-toggle="offcanvas" 
                                                    data-bs-target="#offcanvasSellProduct">
                                                    <i class="bi bi-cart"></i>
                                                </a>

                                                <a href="#"
                                                    class="btn btn-sm btn-square btn-primary edit-product"
                                                    data-id="<?= $product['id'] ?>"
                                                    data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                                    data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                                                    data-price="<?= htmlspecialchars($product['price'], ENT_QUOTES) ?>"
                                                    data-stock="<?= htmlspecialchars($product['stock'], ENT_QUOTES) ?>"
                                                    data-image="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>"
                                                    data-status="<?= htmlspecialchars($product['status'], ENT_QUOTES) ?>"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#offcanvasEditProduct">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-product" 
                                                    data-id="<?= $product['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($product['name']) ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmActionModal">
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                    <div style="position: relative; height: 250px;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                            <img src="./assets/img/no-data.png" width="150" alt="No Products">
                                            <p class="mt-3 lead">No products yet</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php 
    include "./modal/new-product-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/edit-product-offcanvas.php";
    include "./modal/product-sale-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#product').DataTable();
        });
    </script>

    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }
    </script>

    <!-- Sell product -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const notyf = new Notyf();

            const saleButtons = document.querySelectorAll('.sale-product');

            const idInput = document.getElementById('sellProductId');
            const nameInput = document.getElementById('sellProductName');
            const priceInput = document.getElementById('sellProductPrice');
            const qtyInput = document.getElementById('sellQuantity');
            const totalInput = document.getElementById('sellTotal');
            const imagePreview = document.getElementById('sellProductImage');

            const hiddenPrice = document.getElementById('hiddenPrice');
            const hiddenTotal = document.getElementById('hiddenTotal');

            const form = document.getElementById('sellProductForm');
            const submitBtn = form.querySelector('button[type="submit"]');

            let currentStock = 0;

            function formatNumber(num) {
                return new Intl.NumberFormat().format(num);
            }

            // === Open Sell Canvas ===
            saleButtons.forEach(button => {
                button.addEventListener('click', () => {

                    const id = button.dataset.id;
                    const name = button.dataset.name;
                    const price = parseFloat(button.dataset.price);
                    const stock = parseInt(button.dataset.stock);
                    const image = button.dataset.image;

                    currentStock = stock;

                    idInput.value = id;
                    nameInput.value = name;

                    priceInput.value = formatNumber(price);
                    hiddenPrice.value = price;

                    qtyInput.value = 1;
                    qtyInput.max = stock;

                    totalInput.value = formatNumber(price);
                    hiddenTotal.value = price;

                    // Image preview
                    if (image) {
                        imagePreview.src = image;
                        imagePreview.style.display = 'block';
                    } else {
                        imagePreview.style.display = 'none';
                    }
                });
            });

            // === Auto Calculate Total ===
            qtyInput.addEventListener('input', () => {
                let price = parseFloat(hiddenPrice.value) || 0;
                let qty = parseInt(qtyInput.value) || 1;

                if (qty > currentStock) {
                    qty = currentStock;
                    qtyInput.value = currentStock;
                    notyf.error('Cannot exceed available stock');
                }

                const total = price * qty;

                totalInput.value = formatNumber(total);
                hiddenTotal.value = total;
            });

            // === Submit ===
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                const formData = new FormData(form);

                try {
                    const response = await fetch('./auth/create_transaction_auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        notyf.error(data.message);
                    }

                } catch (error) {
                    console.error(error);
                    notyf.error('Network error.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `Complete Sale`;
                }
            });

        });
    </script>

    <!-- Create product -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewProduct form');
            if (!form) return; // safely skip if form not found

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Creating...`;

                try {
                const response = await fetch('./auth/create_product_auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    notyf.success(data.message);
                    form.reset();
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    notyf.error(data.message);
                }
                } catch (error) {
                notyf.error('Network or server error.');
                console.error(error);
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>Add New Product`;
            });
        });
    </script>

    <!-- Edit product -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditProduct');
            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-product');

            // === Handle "Edit" button click ===
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Get product data from button
                    const id = button.dataset.id;
                    const name = button.dataset.name;
                    const description = button.dataset.description;
                    const price = button.dataset.price;
                    const stock = button.dataset.stock;
                    const status = button.dataset.status;

                    // Fill form
                    form.querySelector('[name="id"]').value = id;
                    form.querySelector('[name="name"]').value = name;
                    form.querySelector('[name="description"]').value = description;
                    form.querySelector('[name="price"]').value = price;
                    form.querySelector('[name="stock"]').value = stock;
                    form.querySelector('[name="status"]').value = status;

                    // Update button text
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `Update Product`;

                    // Show offcanvas
                    const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
                    offcanvas.show();
                });
            });

            // === Handle form submission ===
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                    const formData = new FormData(form);

                    try {
                        const response = await fetch('./auth/update_product_auth.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) {
                            throw new Error('Server error');
                        }

                        const data = await response.json();

                        if (data.success) {
                            notyf.success(data.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            notyf.error(data.message);
                        }

                    } catch (error) {
                        console.error(error);
                        notyf.error('Network or server error.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = `Update Product`;
                    }
                });
            }
        });
    </script>

    <!-- Delete product -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE USER =========
            document.querySelectorAll('.delete-product').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this product';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentUserId || !currentAction) return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const response = await fetch('./auth/delete_product_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentUserId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message || 'Operation failed.');
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error('Network or server error.');
                }

                confirmButton.disabled = false;
                confirmButton.textContent = 'Delete';
            });
        });
    </script>



</body>

</html>