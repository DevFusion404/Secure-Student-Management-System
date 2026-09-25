/*
 * Page behaviour that used to live in inline <script> blocks.
 *
 * The Content-Security-Policy (see security.php) only allows scripts loaded
 * from this site, so inline scripts are blocked. Every page loads this file
 * after its vendor libraries; each step checks that its plugin and target
 * elements exist, so the same file is safe on every page.
 */
$(function () {

  // Time pickers (Gentelella pages).
  if ($.fn.datetimepicker) {
    $('#myDatepicker3, #myDatepicker4').datetimepicker({
      format: 'hh:mm A'
    });
  }

  // Tables (attendancelist.php, schedule-stu.php).
  if ($.fn.DataTable) {
    $('#example1').DataTable();
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    });
  }

  if ($.fn.select2) {
    $('.select2').select2();
  }

  if ($.fn.datepicker) {
    $('#datepicker').datepicker({
      autoclose: true
    });
  }

  if ($.fn.timepicker) {
    $('.timepicker').timepicker({
      showInputs: false
    });
  }

  // Highlight the current sidebar entry: <body data-active-menu="attendance">.
  var activeMenu = document.body.getAttribute('data-active-menu');
  if (activeMenu) {
    $('#' + activeMenu).addClass('active');
  }

  // Ask before submitting a delete form.
  $('form.delete-user, form.delete-notice').on('submit', function () {
    return confirm('Are you sure you want to delete?');
  });

  // The server renders <span class="js-show-truemsg"> after a successful save;
  // reveal the success alert once the whole page (and #truemsg) exists.
  if ($('.js-show-truemsg').length) {
    $('#truemsg').show();
  }
});
