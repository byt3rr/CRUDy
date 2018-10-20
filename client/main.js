$(document).ready( function () {
    
    createTable = function(data) {
        const columns = data;


        // Since we are prepending, we will reverse the titles.
        columns.reverse();

        // Titles that will be appended to the HTML
        columns.forEach(column => {
            if (column.show) {
                $("#CRUDyTable thead tr").prepend(`<th>${column.title}</th>`);
            }
        });

        // Now that the columns are in order, lets reverse again to the original state.
        columns.reverse();

        // Actual column defenitions as they appear in the DB, for correct functionality of DataTables.
        columnsList = []
        columns.forEach(column => {
            if(column.show){
                newColumn = {data: `${column.nameInTable}`}
                columnsList.push(newColumn);
            }
        });
        
        // Add Edit and Delete columns, for Datatables to render correctly
        columnsList = [...columnsList,
            {
                data: null,
                sortable: false,
                render: function () {return "<button class='btn btn-default'>Edit</button>"}
            }
        ]
        
        // Create DataTable instance
        const table = $("#CRUDyTable").DataTable({
            ajax : {
                url: "../server/myController.php",
                dataSrc: ''
            },
            columns: columnsList,
            select: true
        });

        // Prevent select when pressing Edit button.
        table.on( 'user-select', function ( e, dt, type, cell, originalEvent ) {
            if ( $(originalEvent.target).is("button")) {
                e.preventDefault();
            }
        } );



        /* Modal Event handeling code */
        
        $('#CRUDyTable tbody').on( 'click', 'button', function () {
            var data = table.row( $(this).parents('tr') ).data();
            $("#editModal").modal('show');
            currentRowData = data;
            createEditModal();
        });

        

        $('#deleteButton').on( 'click', function () {
            var ids = []
            table.rows( { selected: true } ).every(function () {
                ids = [...ids, parseInt(this.data().id)];
            });
            $.ajax({
                url: '../server/myController.php',
                method: 'DELETE',
                data: JSON.stringify(ids),
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    table.ajax.reload();
                }
            })
        });
        $("#newRowButton").click(function (e) { 
            e.preventDefault();
            $("#newModal").modal('show');
        });


        var bsColCount = 0;        
        /* Modal form creation code */

        columns.forEach(column => {
            if(column.show){
                var formgroup = "";
                if(bsColCount === 0){
                    $("#newRowForm .container").append("<div class='row'></div>");
                } else if (bsColCount === 3){
                    $("#newRowForm .container").append("<div class='row'></div>");
                    bsColCount = 0;
                }
                
                domId = `${column.nameInTable}Input`;
                formgroup = "";

                if (column.type === "checkbox") {
                    formgroup = 
                    `<div class="col-md-4">`+
                        `<div class="form-group form-check">`+
                        `<input class="form-check-input" type="${column.type}" name="${column.nameInTable}" value="true" id="${domId}">`+
                            `<label class="form-check-label" for="${domId}">${column.title}</label>`+
                        `</div>`+
                    `</div>`;
                } else {
                    formgroup = 
                    `<div class="col-md-4">`+
                        `<div class="form-group">`+
                            `<label for="${domId}">${column.title}</label>`+
                            `<input type="${column.type}" name="${column.nameInTable}" class="form-control" id="${domId}">`+
                        `</div>`+
                    `</div>`;
                }
    
                $("#newRowForm .row:last-child").append(formgroup);
                bsColCount++;

            }
        });

        // Edit Modal creation
        createEditModal = function() {
            var rowData = currentRowData;
            $("#editModalForm .container").empty();
            $("#editModalForm .container").append(`<input type="hidden" name="id" value="${rowData.id}">`)
            var bsColCount = 0; 
            columns.forEach(column => {
                if(column.show){
                    

                    var formgroup = "";
                    if(bsColCount === 0){
                        $("#editModalForm .container").append("<div class='row'></div>");
                    } else if (bsColCount === 3){
                        $("#editModalForm .container").append("<div class='row'></div>");
                        bsColCount = 0;
                    }
                    
                    domId = `${column.nameInTable}Edit`;
                    formgroup = "";

                    if (column.type === "checkbox") {
                        formgroup = 
                        `<div class="col-md-4">`+
                            `<div class="form-group form-check">`;
                        if (rowData[column.nameInTable] === "1") {
                            formgroup += `<input class="form-check-input" type="${column.type}" name="${column.nameInTable}" checked value="true" id="${domId}">`;
                        } else {
                            formgroup += `<input class="form-check-input" type="${column.type}" name="${column.nameInTable}" value="true" id="${domId}">`;
                        }
                         formgroup +=   
                                `<label class="form-check-label" for="${domId}">${column.title}</label>`+
                            `</div>`+
                        `</div>`;
                    } else {
                        formgroup = 
                        `<div class="col-md-4">`+
                            `<div class="form-group">`+
                                `<label for="${domId}">${column.title}</label>`+
                                `<input type="${column.type}" name="${column.nameInTable}" value="${rowData[column.nameInTable]}" class="form-control" id="${domId}">`+
                            `</div>`+
                        `</div>`;
                    }
                    $("#editModalForm .row:last-child").append(formgroup);
                    bsColCount++;

                }
            });
        }
        

        // Create Row handler.
        $("#createFormSubmit").click(function (e) { 
            e.preventDefault();
            var data = prepareJSON($("#newRowForm").serializeArray());
            console.log(data);
            $.ajax({
                url: '../server/myController.php',
                method: 'POST',
                data: JSON.stringify(data),
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    $("#newModal").modal('hide');
                    table.ajax.reload();
                }
            })
        });

        // Edit Row handler.
        $("#editModalSubmit").click(function (e) { 
            e.preventDefault();
            var data = prepareJSON($("#editModalForm").serializeArray());
            

            // serialize array will not include unchecked checkboxes, so we make sure to add them to the JSON here.
            columns.forEach(column => {
                if(column.type === "checkbox"){
                    if(!$(`#editModalForm #${column.nameInTable}Edit`).prop("checked")){
                            data[column.nameInTable] = "false" ;
                        }
                    }
            });


            $.ajax({
                url: '../server/myController.php',
                method: 'PUT',
                data: JSON.stringify(data),
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    $("#editModal").modal('hide');
                    table.ajax.reload();
                }
            })
        });

        
    }


    // Create the table, fetch all the data needed.
    $.get("../server/columns.json", createTable);

    function prepareJSON(json) {
        properJSON = {};
        json.forEach(field => {
            if (field.value != "") {
                if (field.value === "true") {
                    properJSON[field.name] = true;
                } else if (field.value === "false") {
                    properJSON[field.name] = false;
                } else {
                    properJSON[field.name] = field.value;
                }
            }
        });
        return properJSON
    }


});