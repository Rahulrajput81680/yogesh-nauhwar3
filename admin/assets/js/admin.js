/**
 * Universal Admin Panel JavaScript
 */

$(document).ready(function () {
  // ── Sidebar: restore collapse state across page navigations ──────────────
  var sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
  if (sidebarCollapsed) {
    $("#sidebar").addClass("active");
    $("#content").addClass("active");
  }

  // Scroll active sidebar item into view without hiding items above it.
  // scrollIntoView with block:'nearest' only scrolls the minimum needed.
  var $activeLink = $("#sidebar ul.components li.active a");
  if ($activeLink.length) {
    $activeLink[0].scrollIntoView({ block: 'nearest', behavior: 'auto' });
  }

  // Sidebar toggle
  $("#sidebarCollapse").on("click", function () {
    $("#sidebar").toggleClass("active");
    $("#content").toggleClass("active");
    localStorage.setItem('sidebarCollapsed', $("#sidebar").hasClass("active") ? '1' : '0');
  });

  // Auto-generate slug from title
  $("#title").on("blur", function () {
    var title = $(this).val();
    if (title && (!$("#slug").val() || $("#slug").data("auto-generated"))) {
      var slug = generateSlug(title);
      $("#slug").val(slug).data("auto-generated", true);
    }
  });

  $("#slug").on("input", function () {
    $(this).data("auto-generated", false);
  });

  // Image preview (skipped for inputs that have their own custom preview handler)
  $('input[type="file"]').on("change", function (e) {
    var maxUploadSize = Number(window.ADMIN_MAX_UPLOAD_SIZE || 0);
    var files = e.target.files || [];

    $(this).siblings('.upload-size-error').remove();

    if (maxUploadSize > 0 && files.length > 0) {
      for (var i = 0; i < files.length; i++) {
        if (files[i].size > maxUploadSize) {
          var msg = 'File size exceeds the maximum allowed size of ' + formatBytes(maxUploadSize) + '.';
          $('<small class="text-danger upload-size-error d-block mt-1"></small>').text(msg).insertAfter($(this));
          this.value = '';
          return;
        }
      }
    }

    if ($(this).data('no-generic-preview')) return;
    var file = e.target.files[0];
    if (file && file.type.startsWith("image/")) {
      var reader = new FileReader();
      reader.onload = function (e) {
        var preview = $(this).siblings(".image-preview");
        if (preview.length === 0) {
          preview = $('<img class="image-preview mt-2" alt="Preview">');
          $(this).parent().append(preview);
        }
        preview.attr("src", e.target.result).show();
      }.bind(this);
      reader.readAsDataURL(file);
    }
  });

  // Confirm delete actions
  $(".btn-delete, .delete-btn").on("click", function (e) {
    if (
      !confirm(
        "Are you sure you want to delete this item? This action cannot be undone.",
      )
    ) {
      e.preventDefault();
      return false;
    }
  });

  // Auto-dismiss alerts after 5 seconds
  setTimeout(function () {
    $(".alert").fadeOut("slow", function () {
      $(this).remove();
    });
  }, 5000);

  // DataTable initialization (if present)
  if ($.fn.DataTable) {
    $(".data-table").DataTable({
      pageLength: 10,
      order: [[0, "desc"]],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search...",
      },
    });
  }

  // Character counter for textarea
  $("textarea[maxlength]").each(function () {
    var maxLength = $(this).attr("maxlength");
    var currentLength = $(this).val().length;
    var counter = $('<div class="char-counter text-muted small mt-1"></div>');
    counter.text(currentLength + " / " + maxLength + " characters");
    $(this).after(counter);

    $(this).on("input", function () {
      var length = $(this).val().length;
      counter.text(length + " / " + maxLength + " characters");
    });
  });

  // Bulk action checkboxes
  $("#selectAll").on("change", function () {
    $(".item-checkbox").prop("checked", $(this).prop("checked"));
    updateBulkActionButtons();
  });

  $(".item-checkbox").on("change", function () {
    updateBulkActionButtons();
  });

  // Bulk delete
  $("#bulkDelete").on("click", function (e) {
    e.preventDefault();
    var selected = $(".item-checkbox:checked");
    if (selected.length === 0) {
      alert("Please select at least one item.");
      return;
    }
    if (
      confirm(
        "Are you sure you want to delete " + selected.length + " item(s)?",
      )
    ) {
      var ids = [];
      selected.each(function () {
        ids.push($(this).val());
      });
      $("#bulkIds").val(ids.join(","));
      $("#bulkForm").submit();
    }
  });
});

/**
 * Generate slug from string
 */
function generateSlug(str) {
  return str
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, "")
    .replace(/[\s_-]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function formatBytes(bytes) {
  var units = ['B', 'KB', 'MB', 'GB'];
  var value = bytes;
  var unitIndex = 0;

  while (value >= 1024 && unitIndex < units.length - 1) {
    value = value / 1024;
    unitIndex++;
  }

  return Math.round(value * 100) / 100 + ' ' + units[unitIndex];
}

/**
 * Update bulk action buttons visibility
 */
function updateBulkActionButtons() {
  var selectedCount = $(".item-checkbox:checked").length;
  if (selectedCount > 0) {
    $(".bulk-actions").show();
    $(".bulk-count").text(selectedCount);
  } else {
    $(".bulk-actions").hide();
  }
}

/**
 * Show loading spinner
 */
function showLoading() {
  $("body").append(
    '<div class="spinner-overlay"><div class="spinner-border text-light" role="status"></div></div>',
  );
}

/**
 * Hide loading spinner
 */
function hideLoading() {
  $(".spinner-overlay").remove();
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
  var temp = $("<input>");
  $("body").append(temp);
  temp.val(text).select();
  document.execCommand("copy");
  temp.remove();
  alert("Copied to clipboard!");
}
