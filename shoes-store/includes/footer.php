    </main>
    <!-- End Main Content -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Section -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3">KICKS & COMFORT</h5>
                    <p class="text-secondary small">Engineered for the modern athlete. Premium footwear designed for performance, style, and everyday comfort.</p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">GET HELP</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>user/orders.php">Order Status</a></li>
                        <li><a href="<?= BASE_URL ?>services.php">Shipping & Delivery</a></li>
                        <li><a href="<?= BASE_URL ?>services.php">Returns</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Shop Links -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">SHOP</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>products.php">New Arrivals</a></li>
                        <li><a href="<?= BASE_URL ?>products.php?category=men">Men's Shoes</a></li>
                        <li><a href="<?= BASE_URL ?>products.php?category=women">Women's Shoes</a></li>
                        <li><a href="<?= BASE_URL ?>products.php?category=sale">Sale</a></li>
                    </ul>
                </div>
                
                <!-- Social -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">FOLLOW US</h6>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <hr style="border-color: #333; margin: 40px 0;">
            
            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-secondary small">&copy; <?= date('Y') ?> Kicks & Comfort, Inc. All Rights Reserved</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-secondary small">
                        <a href="#" class="d-inline text-secondary me-3">Guides</a>
                        <a href="#" class="d-inline text-secondary me-3">Terms of Use</a>
                        <a href="#" class="d-inline text-secondary">Privacy Policy</a>
                    </p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col text-center">
                    <p class="mb-0 text-secondary small">
                        Developed By Tushar Solanki
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Container -->
    <div id="toastContainer"></div>
    
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    
    <!-- jQuery -->
    <script src="<?= ASSETS_URL ?>js/lib/jquery.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery Validation -->
    <script src="<?= ASSETS_URL ?>js/lib/jquery.validate.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?= ASSETS_URL ?>js/validation.js"></script>
    <script src="<?= ASSETS_URL ?>js/main.js"></script>
    <script src="<?= ASSETS_URL ?>js/wishlist.js?v=<?= time() ?>"></script>
    
    <!-- Additional Page Scripts -->
    <?php if (isset($extra_scripts)): ?>
        <?= $extra_scripts ?>
    <?php endif; ?>
    
</body>
</html>
