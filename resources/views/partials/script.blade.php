<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
<script src="{{ asset('asset/js/scripts.js') }}"></script>
<script src="{{ asset('js/latest.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('asset/js/datatables/datatables-simple-demo.js') }}"></script>
<script src="{{ asset('cdn.jsdelivr.net/npm/litepicker/dist/bundle.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('asset/js/litepicker.js') }}"></script>
<script src="{{ asset('js/jquery-3.5.1.js') }}"></script>
<script src="{{ asset('js/jqueryDataTable.min.js') }}"></script>
<script src="{{ asset('js/jqueryDataTableButtons.min.js') }}"></script>
<script src="{{ asset('js/jszip.min.js') }}"></script>
<script src="{{ asset('js/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/vfs_fonts.min.js') }}"></script>
<script src="{{ asset('js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('js/buttons.print.min.js') }}"></script>
<script src="{{ asset('js/buttons.colvis.min.js') }}"></script>

<script type="text/javascript">
    $('#datatablesSimple').DataTable({
        dom: 'Blfrtip',
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Tous"]
        ],
        buttons: [

            {
                extend: 'colvis',
                text: 'Visibilité des colonnes',

            },
            {
                extend: 'print',
                text: 'Imprimer',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'pdfHtml5',
                text: ' PDF',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'excelHtml5',
                text: 'EXCEL<br>',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },
        ]



    });

    $('#datatablesSimple1').DataTable({
        dom: 'Blfrtip',
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Tous"]
        ],
        buttons: [

            {
                extend: 'colvis',
                text: 'Visibilité des colonnes',

            },
            {
                extend: 'print',
                text: 'Imprimer',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'pdfHtml5',
                text: ' PDF',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'excelHtml5',
                text: 'EXCEL<br>',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },
        ]


    });
</script>
<script src="{{ asset('assets.startbootstrap.com/js/sb-customizer.js') }}"></script>

{{-- Custom scripts for all pages --}}
<script>
    function searchTable() {
        // Récupérer la valeur de recherche et la convertir en minuscules
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase().trim();

        // Récupérer le tableau
        const table = document.querySelector('.table');
        const tbody = table.querySelector('tbody');
        const rows = tbody.getElementsByTagName('tr');

        // Compteur pour les résultats trouvés
        let visibleRows = 0;

        // Parcourir toutes les lignes du tableau
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];

            // Ignorer les lignes de modal (qui ne sont pas des lignes de données)
            if (row.classList.contains('modal')) {
                continue;
            }

            // Récupérer toutes les cellules de la ligne (sauf la dernière colonne "Action")
            const cells = row.getElementsByTagName('td');
            let found = false;

            // Parcourir les cellules (colonnes 0 à 5: Code, Désignation, Adresse, IFU, Catégorie, Téléphone)
            for (let j = 0; j < cells.length - 1; j++) {
                const cellText = cells[j].textContent || cells[j].innerText;

                // Si le texte de la cellule contient le filtre de recherche
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }

            // Afficher ou masquer la ligne en fonction du résultat
            if (found) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        }

        // Optionnel: Afficher un message si aucun résultat n'est trouvé
        showNoResultsMessage(visibleRows, tbody, table);
    }

    function showNoResultsMessage(visibleRows, tbody, table) {
        // Supprimer le message précédent s'il existe
        const existingMessage = document.getElementById('noResultsMessage');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Si aucun résultat n'est trouvé, afficher un message
        if (visibleRows === 0) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsMessage';
            noResultsRow.innerHTML = `
            <td colspan="7" class="text-center text-muted py-4">
                <i class="fa fa-search" aria-hidden="true"></i>
                <p class="mb-0 mt-2">Aucun résultat trouvé</p>
            </td>
        `;
            tbody.appendChild(noResultsRow);
        }
    }

    // Optionnel: Ajouter un indicateur de recherche en temps réel
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
            // Ajouter une icône de recherche dans l'input
            searchInput.classList.add('ps-5');

            // Effacer la recherche avec un bouton
            searchInput.addEventListener('input', function() {
                if (this.value === '') {
                    searchTable();
                }
            });
        }
    });
</script>
