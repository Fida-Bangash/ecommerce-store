{{-- START: Footer Component --}}
<footer class="footer-custom">
  <div class="footer-left">
    <span class="footer-logo">
      <i class="bi bi-asterisk"></i> {{ config('app.name') }}
    </span>
    <span class="footer-separator">|</span>
    <span class="footer-copy">&copy; {{ date('Y') }} Made with <i class="bi bi-heart-fill text-danger footer-heart"></i></span>
  </div>
  <div class="footer-right">
    <ul class="footer-links">
      <li><a href="#" class="footer-link">Overview</a></li>
      <li><a href="#" class="footer-link">Statistics</a></li>
      <li><a href="#" class="footer-link">Help &amp; Documentation</a></li>
      <li><a href="#" class="footer-link">Status <span class="status-dot"></span></a></li>
    </ul>
  </div>
</footer>
{{-- END: Footer Component --}}
