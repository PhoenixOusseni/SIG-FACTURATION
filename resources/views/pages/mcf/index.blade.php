@extends('layouts.master')
@section('title')
    <title>SIG-FACTURATION | Console Boîtier Fiscal</title>
@endsection
@section('style')
    @include('partials.style')
@endsection
@section('content')
    <main>
        @include('pages.mcf.console')
    </main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable && $('#mcfCertificationsTable').length && !$.fn.DataTable.isDataTable('#mcfCertificationsTable')) {
            $('#mcfCertificationsTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Toutes"]],
                language: {
                    processing: "Traitement en cours...",
                    search: "Recherche :",
                    lengthMenu: "Afficher _MENU_ certifications",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ certifications",
                    infoEmpty: "Affichage de 0 à 0 sur 0 certification",
                    infoFiltered: "(filtré sur _MAX_ certifications)",
                    zeroRecords: "Aucune certification trouvée",
                    emptyTable: "Aucune certification enregistrée",
                    paginate: {
                        first: "Premier",
                        previous: "<i class='fas fa-chevron-left me-1'></i> Précédent",
                        next: "Suivant <i class='fas fa-chevron-right ms-1'></i>",
                        last: "Dernier"
                    }
                }
            });
        }
    });
</script>
@endsection
