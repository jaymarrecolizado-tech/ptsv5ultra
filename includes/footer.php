<?php
/**
 * Common footer template
 */
?>

<?php if (isLoggedIn()): ?>
            </div>
        </main>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    
    <!-- Leaflet MarkerCluster -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Application Scripts -->
    <script src="/projects/newPTS/assets/js/api.js"></script>
    <script src="/projects/newPTS/assets/js/validation.js"></script>
    <script src="/projects/newPTS/assets/js/map.js"></script>
    <script src="/projects/newPTS/assets/js/charts.js"></script>
    <script src="/projects/newPTS/assets/js/app.js"></script>
<?php endif; ?>

</body>
</html>
