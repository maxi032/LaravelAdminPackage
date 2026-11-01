<script>
    try {
        if (localStorage.getItem('laravel-admin.sidebar') === 'collapsed') {
            document.getElementById('sidebar').classList.add('sidebar-narrow');
        }
    } catch (error) {
        // Storage may be unavailable in private or restricted browsing contexts.
    }
</script>
