                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewProduct" aria-labelledby="offcanvasCreateLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasCreateLabel">Create a new product</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form>
                            <div class="row g-5">
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Product name</label>
                                        <input type="text" class="form-control" placeholder="Enter product name" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Product description</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Description" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="last_name">Price</label> 
                                    <input type="number" class="form-control" step="0.01" name="price" placeholder="Price" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="last_name">Stock quantity</label> 
                                    <input type="number" class="form-control" name="stock" placeholder="Stock" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Product image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" required>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>Add Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>