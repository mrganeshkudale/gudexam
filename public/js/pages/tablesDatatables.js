/*
 *  Document   : tablesDatatables.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Tables Datatables page
 */

var TablesDatatables = function() {

    return {
        init: function() {
            /* Initialize Bootstrap Datatables Integration */
            App.datatables();

            /* Initialize Datatables */
            $('#myTable').dataTable({
                
            });

            $('#myTable1').dataTable({
                
            });

            /* Add placeholder attribute to the search input */
            $('.dataTables_filter input').attr('placeholder', 'Search');
        }
    };
}();