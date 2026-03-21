                <div class="offcanvas offcanvas-end w-full w-lg-1/3" tabindex="-1" id="offcanvasEditProduct" aria-labelledby="offcanvasEditProductLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasEditProductLabel">Edit Prodct</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-4">
                        <form id="editProductForm" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="editProductId">

                            <div class="col-md-12">
                                <label class="form-label">Product name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Product description</label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Price</label> 
                                <input type="number" class="form-control" step="0.01" name="price" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Stock quantity</label> 
                                <input type="number" class="form-control" name="stock" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Product image (optional)</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-full">
                                Update Product
                            </button>

                        </form>
                    </div>
                </div>