</div>
<!-- End Main Content Area -->

<!-- Footer -->
<!-- <footer class="footer mt-auto py-3 bg-light border-top">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6 text-muted">
        &copy; <?php echo date('Y'); ?> <?php echo escape(PROJECT_NAME); ?>. All rights reserved.
      </div>
      <div class="col-md-6 text-end text-muted">
        Designed by Digiconn Unite pvt Ltd
      </div>
    </div>
  </div>
</footer>
</div>
</div> -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional, for easier DOM manipulation) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  window.ADMIN_MAX_UPLOAD_SIZE = <?php echo (int) MAX_UPLOAD_SIZE; ?>;
</script>

<!-- Custom Admin Scripts -->
<script src="<?php echo ADMIN_URL; ?>/assets/js/admin.js"></script>

<?php if (isset($extra_js)): ?>
  <?php echo $extra_js; ?>
<?php endif; ?>
</body>

</html>