// const quantityModal = document.getElementById('quantityModal');
// quantityModal.addEventListener('show.bs.modal', function (event) {
//     const button = event.relatedTarget;
//     const productId = button.getAttribute('data-product-id');
//     const productName = button.getAttribute('data-product-name');

//     document.getElementById('modal_product_id').value = productId;
//     document.getElementById('modal_product_name').textContent = productName;
// });

    const ROUTES = {
        treeData: "{{ route('chart_of_accounts.tree_data') }}",
        summaryTotals: "{{ route('chart_of_accounts.summary_totals') }}",
        groupDetails: "{{ url('group') }}", // base URL only
        ledgerDetails: "{{ url('chart-of-accounts/ledger') }}", // base URL only
        deleteGroup: "{{ url('chart-of-accounts/group') }}", // base URL only
        deleteLedger: "{{ url('chart-of-accounts/ledger') }}" // base URL only
    };
    
 $(document).ready(function() {
    let selectedId = null;
    let selectedType = null;
    

  
    
    // Initialize tree
    $('#account-tree').jstree({
        'core': {
            'data': {
                'url': ROUTES.treeData,
                'dataType': 'json'
            },
            'themes': {
                'dots': true,
                'icons': true
            }
        },
        'types': {
            'group': {
                'icon': 'fas fa-folder'
            },
            'ledger': {
                'icon': 'fas fa-file-alt'
            }
        },
        'plugins': ['types', 'search']
    });
    
    // Handle node selection
    $('#account-tree').on('select_node.jstree', function(e, data) {
   
            $('#details-panel').html('<p class="text-center text-muted">You do not have permission to view details</p>');
            return;
        
        
        if (data.node.type === 'ledger') {
            selectedType = 'ledger';
            selectedId = data.node.data.id;
            loadLedgerDetails(selectedId);
        } else if (data.node.type === 'group') {
            selectedType = 'group';
            selectedId = data.node.data.id;
            loadGroupDetails(selectedId);
        }
    });
    
    // Load group details
    function loadGroupDetails(groupId) {
        $.ajax({
            url: ROUTES.groupDetails + '/' + groupId + '/details',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let group = response.group;
                    let isFixed = group.fixed == 1;
                    
                    let html = `
                        <h5>Group Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Group Name:</th>
                                <td>${group.name}</td>
                            </tr>
                            <tr>
                                <th>Group Code:</th>
                                <td>${group.code}</td>
                            </tr>
                            <tr>
                                <th>Parent Group:</th>
                                <td>${group.parent ? group.parent.name + ' (' + group.parent.code + ')' : 'None (Top Level)'}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>${isFixed ? '<span class="badge badge-warning">System Group</span>' : '<span class="badge badge-success">User Created</span>'}</td>
                            </tr>
                            <tr>
                                <th>Sub-groups:</th>
                                <td>${group.children.length}</td>
                            </tr>
                            <tr>
                                <th>Ledgers:</th>
                                <td>${group.ledgers.length}</td>
                            </tr>
                        </table>
                        <div class="mt-3">
                            ${!isFixed ? `
                                <a href="${ROUTES.deleteGroup}/${group.id}/edit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            ` : ''}
                            ${!isFixed  ? `
                                <button class="btn btn-danger btn-sm" onclick="deleteGroup(${group.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            ` : ''}
                            ${isFixed ? '' : ''}
                        </div>
                    `;
                    
                    $('#details-panel').html(html);
                }
            },
            error: function() {
                $('#details-panel').html('<p class="text-center text-danger">Error loading group details</p>');
            }
        });
    }
    
    // Load ledger details
    function loadLedgerDetails(ledgerId) {
        $.ajax({
            url: ROUTES.ledgerDetails + '/' + ledgerId + '/details',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let ledger = response.ledger;
                    let balance = ledger.opening_balance ? ledger.opening_balance[0] : null;
                    
                    let html = `
                        <h5>Ledger Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Ledger Name:</th>
                                <td>${ledger.name}</td>
                            </tr>
                            <tr>
                                <th>Ledger Code:</th>
                                <td>${ledger.left_code || ''} / ${ledger.right_code || ''}</td>
                            </tr>
                            <tr>
                                <th>Group:</th>
                                <td>${ledger.group.name} (${ledger.group.code})</td>
                            </tr>
                            <tr>
                                <th>Opening Balance:</th>
                                <td>${balance ? 'RM ' + Number(balance.dr_amount - balance.cr_amount).toFixed(2) : 'RM 0.00'}</td>
                            </tr>
                            <tr>
                                <th>Features:</th>
                                <td>
                                    ${ledger.type == 1 ? '<span class="badge badge-info">Bank/Cash</span>' : ''}
                                    ${ledger.reconciliation ? '<span class="badge badge-success">Reconciliation</span>' : ''}
                                    ${ledger.pa ? '<span class="badge badge-warning">P&L Accumulation</span>' : ''}
                                    ${ledger.aging ? '<span class="badge badge-primary">Aging</span>' : ''}
                                    ${ledger.credit_aging ? '<span class="badge badge-danger">Credit Aging</span>' : ''}
                                    ${ledger.iv ? '<span class="badge badge-secondary">Inventory</span>' : ''}
                                </td>
                            </tr>
                            ${ledger.notes ? `<tr><th>Notes:</th><td>${ledger.notes}</td></tr>` : ''}
                        </table>
                        <div class="mt-3">
                         
                                <a href="${ROUTES.ledgerDetails}/${ledger.id}/view" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                           
                          
                                <a href="${ROUTES.ledgerDetails}/${ledger.id}/edit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                          
                      
                                <button class="btn btn-danger btn-sm" onclick="deleteLedger(${ledger.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                  
                        </div>
                    `;
                    
                    $('#details-panel').html(html);
                }
            },
            error: function() {
                $('#details-panel').html('<p class="text-center text-danger">Error loading ledger details</p>');
            }
        });
    }
    
    // Delete group
    window.deleteGroup = function(groupId) {

   
        
        selectedId = groupId;
        selectedType = 'group';
        $('#deleteMessage').text('Are you sure you want to delete this group?');
        $('#deleteModal').modal('show');
    }
    
    // Delete ledger
    window.deleteLedger = function(ledgerId) {
     
        
        selectedId = ledgerId;
        selectedType = 'ledger';
        $('#deleteMessage').text('Are you sure you want to delete this ledger?');
        $('#deleteModal').modal('show');
    }
    
    // Handle success messages from localStorage
    const successMessage = localStorage.getItem('delete_success');
    if (successMessage) {
        $('#alert-container').html(`
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${successMessage}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        localStorage.removeItem('delete_success'); // Clear after showing
    }
    
    $('#confirmDelete').click(function() {
        if (selectedId && selectedType) {
            let url = selectedType === 'group' 
             ? `${ROUTES.deleteGroup}/${selectedId}`
            : `${ROUTES.ledgerDetails}/${selectedId}`;
                
            $.ajax({
                url: url,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        localStorage.setItem('delete_success', response.message);
                        
                        // Refresh the tree and totals
                        $('#account-tree').jstree(true).refresh();
                        loadSummary();
                        
                        // Clear details panel
                        $('#details-panel').html('<p class="text-center text-muted">Select a group or ledger from the tree to view details</p>');
                    } else {
                        $('#deleteModal').modal('hide');
                        alert(response.message);
                    }
                },
                error: function() {
                    $('#deleteModal').modal('hide');
                    alert('An error occurred while deleting.');
                }
            });
        }
    });
    
    // Load summary data
    loadSummary();
    
    function loadSummary() {
        // Show loading state
        $('#total-assets').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-liabilities').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-income').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-expenses').html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: ROUTES.summaryTotals,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#total-assets').text('RM ' + response.totals.assets);
                    $('#total-liabilities').text('RM ' + response.totals.liabilities);
                    $('#total-income').text('RM ' + response.totals.income);
                    $('#total-expenses').text('RM ' + response.totals.expenses);
                } else {
                    // Show error state
                    $('.card h4').text('Error loading');
           
                }
            },
            error: function(xhr, status, error) {
               
                $('#total-assets').text('Error');
                $('#total-liabilities').text('Error');
                $('#total-income').text('Error');
                $('#total-expenses').text('Error');
            }
        });
    }
    
});// Enhanced financial summary loading
function loadFinancialSummary() {
    $.ajax({
        url: "{{ route('chart_of_accounts.summary_totals') }}",
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Update main totals
                $('#total-assets').text('RM ' + response.totals.assets);
                $('#total-liabilities').text('RM ' + response.totals.liabilities);
                $('#total-income').text('RM ' + response.totals.income);
                $('#total-expenses').text('RM ' + response.totals.expenses);
                $('#net-profit').text('RM ' + response.totals.net_profit);

                // Update detailed breakdowns
                if (response.detailed) {
                    $('#assets-breakdown').text(
                        'Current: RM ' + response.detailed.assets.current + 
                        ' | Fixed: RM ' + response.detailed.assets.fixed
                    );
                    
                    $('#liabilities-breakdown').text(
                        'Liabilities: RM ' + response.detailed.liabilities.current + 
                        ' | Equity: RM ' + response.detailed.equity.capital
                    );
                    
                    $('#income-breakdown').text(
                        'Revenue: RM ' + response.detailed.income.revenue + 
                        ' | Other: RM ' + response.detailed.income.other_income
                    );
                    
                    $('#expenses-breakdown').text(
                        'Operating: RM ' + response.detailed.expenses.operating_expenses + 
                        ' | Direct: RM ' + response.detailed.expenses.direct_costs
                    );
                }

                // Update percentages
                if (response.percentages) {
                    $('#gross-margin').text('Gross Margin: ' + parseFloat(response.percentages.gross_margin || 0).toFixed(1) + '%');
                    $('#net-margin').text('Net Margin: ' + parseFloat(response.percentages.net_margin || 0).toFixed(1) + '%');
                }

                // Calculate and display KPIs
                calculateKPIs(response);
            }
        },
        error: function() {
            
        }
    });
}

function calculateKPIs(data) {
    if (!data.detailed) return;
    
    const currentAssets = parseFloat(data.detailed.assets.current.replace(/,/g, ''));
    const currentLiabilities = parseFloat(data.detailed.liabilities.current.replace(/,/g, ''));
    const totalLiabilities = parseFloat(data.detailed.liabilities.current.replace(/,/g, '')) + 
                            parseFloat(data.detailed.liabilities.long_term.replace(/,/g, ''));
    const totalEquity = parseFloat(data.detailed.equity.capital.replace(/,/g, '')) + 
                       parseFloat(data.detailed.equity.reserves.replace(/,/g, ''));
    
    // Working Capital
    const workingCapital = currentAssets - currentLiabilities;
    $('#working-capital').text('RM ' + workingCapital.toLocaleString('en-MY', {minimumFractionDigits: 2}));
    
    const workingCapitalRatio = currentLiabilities > 0 ? (currentAssets / currentLiabilities).toFixed(2) : '∞';
    $('#working-capital-ratio').text('Ratio: ' + workingCapitalRatio + ':1');
    
    // Debt to Equity Ratio
    const debtEquityRatio = totalEquity > 0 ? (totalLiabilities / totalEquity).toFixed(2) : '∞';
    $('#debt-equity-ratio').text(debtEquityRatio + ':1');
    
    // Set net profit color
    const netProfit = parseFloat(data.totals.net_profit.replace(/,/g, ''));
    $('#net-profit').css('color', netProfit >= 0 ? '#28a745' : '#dc3545');
    
    // Update profit progress bar
    const totalRevenue = parseFloat(data.detailed.income.revenue.replace(/,/g, ''));
    if (totalRevenue > 0) {
        const profitMargin = (netProfit / totalRevenue) * 100;
        $('#profit-progress').css('width', Math.abs(profitMargin) + '%')
                            .removeClass('bg-success bg-danger')
                            .addClass(profitMargin >= 0 ? 'bg-success' : 'bg-danger');
    }
}

// Load data on page load
$(document).ready(function() {
    loadFinancialSummary();
    
    // Auto-refresh every 5 minutes
    setInterval(loadFinancialSummary, 300000);
});
