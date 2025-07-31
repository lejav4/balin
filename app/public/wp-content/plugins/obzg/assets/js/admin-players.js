(function($){
    function getModalHtml(player, clubs) {
        var isEdit = !!player;
        player = player || {};
        clubs = clubs || [];
        var genderOptions = ['Male', 'Female', 'Other'];
        var selectedClub = player.club_id || '';
        return `
            <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">${isEdit ? 'Edit Player' : 'Add Player'}</h5>
                    <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
                  </div>
                  <form id="obzg-player-form">
                    <div class="modal-body">
                      <input type="hidden" name="player_id" value="${isEdit ? player.id : ''}">
                      <div class="row g-2">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Name</label>
                          <input type="text" class="form-control" name="player_name" value="${player.name || ''}" required />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Surname</label>
                          <input type="text" class="form-control" name="player_surname" value="${player.surname || ''}" required />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" class="form-control" name="player_email" value="${player.email || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Phone Number</label>
                          <input type="text" class="form-control" name="player_number" value="${player.number || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Date of Birth</label>
                          <input type="date" class="form-control" name="player_dob" value="${player.dob || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">City of Birth</label>
                          <input type="text" class="form-control" name="player_city_of_birth" value="${player.city_of_birth || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Gender</label>
                          <select class="form-select" name="player_gender">
                            <option value="">Select...</option>
                            ${genderOptions.map(opt => `<option value="${opt}"${player.gender === opt ? ' selected' : ''}>${opt}</option>`).join('')}
                          </select>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">EMŠO</label>
                          <input type="text" class="form-control" name="player_emso" value="${player.emso || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Club</label>
                          <select class="form-select" name="player_club_id">
                            <option value="">Select club...</option>
                            ${clubs.map(club => `<option value="${club.id}"${String(selectedClub) === String(club.id) ? ' selected' : ''}>${club.title}</option>`).join('')}
                          </select>
                        </div>
                        <div class="col-12 mb-3">
                          <label class="form-label">Address</label>
                          <input type="text" class="form-control" name="player_address" value="${player.address || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">City</label>
                          <input type="text" class="form-control" name="player_city" value="${player.city || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">City Number</label>
                          <input type="text" class="form-control" name="player_city_number" value="${player.city_number || ''}" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Citizenship</label>
                          <input type="text" class="form-control" name="player_citizenship" value="${player.citizenship || ''}" />
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">${isEdit ? 'Save' : 'Add'}</button>
                      <button type="button" class="btn btn-secondary obzg-modal-cancel">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        `;
    }
    function renderPlayersTable(players) {
        if (!players.length) {
            return '<div class="alert alert-info">No players found.</div>';
        }
        var html = '<table class="table table-striped"><thead><tr>' +
            '<th>Name</th>' +
            '<th>Email</th>' +
            '<th>Phone</th>' +
            '<th>Date of Birth</th>' +
            '<th>Actions</th>' +
            '</tr></thead><tbody>';
        players.forEach(function(player){
            html += `<tr>
                <td>${(player.name || '') + ' ' + (player.surname || '')}</td>
                <td>${player.email || ''}</td>
                <td>${player.number || ''}</td>
                <td>${player.dob || ''}</td>
                <td>
                  <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-primary obzg-edit-player-btn" data-id="${player.id}"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-info obzg-more-player-btn" data-id="${player.id}" title="More"><i class="bi bi-info-circle"></i></button>
                    <button class="btn btn-sm btn-outline-danger obzg-delete-player-btn" data-id="${player.id}" title="Delete"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        return html;
    }
    function openPlayerModal(player) {
        // Fetch clubs for the select
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: { action: 'obzg_get_club', _ajax_nonce: OBZG_AJAX_PLAYERS.nonce },
            success: function(clubResp) {
                var clubs = (clubResp.success && Array.isArray(clubResp.data)) ? clubResp.data : [];
                _openPlayerModalWithClubs(player, clubs);
            },
            error: function(){
                _openPlayerModalWithClubs(player, []);
            }
        });
    }
    function _openPlayerModalWithClubs(player, clubs) {
        closePlayerModal();
        var $modal = $(getModalHtml(player, clubs));
        $('body').append($modal);
        setTimeout(function(){ $modal.addClass('show'); }, 10);
        $('.obzg-modal-cancel').on('click', closePlayerModal);
        $('#obzg-player-form').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = {
                action: 'obzg_save_player',
                _ajax_nonce: OBZG_AJAX_PLAYERS.nonce,
                player_id: $form.find('[name="player_id"]').val(),
                player_name: $form.find('[name="player_name"]').val(),
                player_surname: $form.find('[name="player_surname"]').val(),
                player_email: $form.find('[name="player_email"]').val(),
                player_number: $form.find('[name="player_number"]').val(),
                player_dob: $form.find('[name="player_dob"]').val(),
                player_address: $form.find('[name="player_address"]').val(),
                player_city: $form.find('[name="player_city"]' ).val(),
                player_city_number: $form.find('[name="player_city_number"]').val(),
                player_gender: $form.find('[name="player_gender"]').val(),
                player_emso: $form.find('[name="player_emso"]').val(),
                player_city_of_birth: $form.find('[name="player_city_of_birth"]').val(),
                player_citizenship: $form.find('[name="player_citizenship"]').val(),
                player_club_id: $form.find('[name="player_club_id"]').val()
            };
            $form.find('button[type="submit"]').prop('disabled', true);
            $.post(OBZG_AJAX_PLAYERS.ajax_url, data, function(resp){
                $form.find('button[type="submit"]').prop('disabled', false);
                if(resp.success) {
                    closePlayerModal();
                    loadPlayers();
                } else {
                    var msg = resp.data && resp.data.message ? resp.data.message : 'Error saving player.';
                    $form.prepend('<div class="alert alert-danger">'+msg+'</div>');
                }
            }).fail(function(){
                $form.find('button[type="submit"]').prop('disabled', false);
                $form.prepend('<div class="alert alert-danger">AJAX error.</div>');
            });
        });
    }
    function closePlayerModal() {
        $('.obzg-modal').remove();
    }
    function loadPlayers() {
        $('#obzg-player-admin-root').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_player',
                _ajax_nonce: OBZG_AJAX_PLAYERS.nonce
            },
            success: function(resp) {
                if (resp.success && Array.isArray(resp.data)) {
                    ALL_OBZG_PLAYERS = resp.data;
                    // Apply filter if set
                    var clubId = $('#obzg-player-club-filter').val();
                    var filtered = clubId ? ALL_OBZG_PLAYERS.filter(function(p){ return String(p.club_id) === String(clubId); }) : ALL_OBZG_PLAYERS;
                    $('#obzg-player-admin-root').html(renderPlayersTable(filtered));
                } else {
                    $('#obzg-player-admin-root').html('<div class="alert alert-danger">Failed to load players.</div>');
                }
            },
            error: function() {
                $('#obzg-player-admin-root').html('<div class="alert alert-danger">AJAX error loading players.</div>');
            }
        });
    }
    $(document).on('click', '#obzg-add-player-btn', function(){
        openPlayerModal();
    });
    $(document).on('click', '.obzg-edit-player-btn', function(){
        var playerId = $(this).data('id');
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_single_player',
                _ajax_nonce: OBZG_AJAX_PLAYERS.nonce,
                player_id: playerId
            },
            success: function(resp) {
                if (resp.success && resp.data) {
                    openPlayerModal(resp.data);
                } else {
                    alert('Failed to load player data.');
                }
            },
            error: function() {
                alert('AJAX error loading player data.');
            }
        });
    });
    $(function(){
        if($('#obzg-player-admin-root').length) {
            // Render the toolbar (Add Player, Import, Club Filter) in a flex container
            function renderPlayerToolbar() {
                var clubs = window.OBZG_ALL_CLUBS || {};
                var options = '<option value="">All Clubs</option>';
                Object.keys(clubs).forEach(function(cid){
                    options += `<option value="${cid}">${clubs[cid]}</option>`;
                });
                var html = `
                <div class="obzg-player-toolbar mb-3">
                    <button id="obzg-add-player-btn" class="btn btn-success"><i class="bi bi-person-plus"></i> Add Player</button>
                    <button id="obzg-import-players-btn" class="btn btn-secondary"><i class="bi bi-upload"></i> Import from Excel</button>
                    <select id="obzg-player-club-filter" class="form-select" style="max-width:300px;display:inline-block;vertical-align:middle;">
                        ${options}
                    </select>
                </div>`;
                // Remove any previous toolbar
                $('.obzg-player-toolbar').remove();
                $('#obzg-player-admin-root').before(html);
            }

            // On page load, render toolbar after clubs are loaded
            var tryRenderToolbar = function() {
                if (Object.keys(window.OBZG_ALL_CLUBS).length) {
                    renderPlayerToolbar();
                    loadPlayers();
                } else {
                    setTimeout(tryRenderToolbar, 100);
                }
            };
            tryRenderToolbar();
        }
    });

    // Add a global for club lookup
    window.OBZG_ALL_CLUBS = window.OBZG_ALL_CLUBS || {};
    // On page load, fetch all clubs for name lookup
    $(function(){
        if($('#obzg-player-admin-root').length) {
            $.ajax({
                url: OBZG_AJAX_PLAYERS.ajax_url,
                method: 'POST',
                data: { action: 'obzg_get_club', _ajax_nonce: OBZG_AJAX_PLAYERS.nonce },
                success: function(resp) {
                    if(resp.success && Array.isArray(resp.data)) {
                        window.OBZG_ALL_CLUBS = {};
                        resp.data.forEach(function(c){ window.OBZG_ALL_CLUBS[c.id] = c.title; });
                    }
                }
            });
        }
    });

    // More button handler
    $(document).on('click', '.obzg-more-player-btn', function(){
        var playerId = $(this).data('id');
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_single_player',
                _ajax_nonce: OBZG_AJAX_PLAYERS.nonce,
                player_id: playerId
            },
            success: function(resp) {
                if (resp.success && resp.data) {
                    showPlayerDetailsModal(resp.data);
                } else {
                    alert('Failed to load player data.');
                }
            },
            error: function() {
                alert('AJAX error loading player data.');
            }
        });
    });

    // Delete player handler
    $(document).on('click', '.obzg-delete-player-btn', function(){
        var playerId = $(this).data('id');
        if(confirm('Are you sure you want to delete this player?')) {
            $.ajax({
                url: OBZG_AJAX_PLAYERS.ajax_url,
                method: 'POST',
                data: {
                    action: 'obzg_delete_player',
                    _ajax_nonce: OBZG_AJAX_PLAYERS.nonce,
                    player_id: playerId
                },
                success: function(resp) {
                    if(resp.success) {
                        loadPlayers();
                    } else {
                        alert('Failed to delete player.');
                    }
                },
                error: function() {
                    alert('AJAX error deleting player.');
                }
            });
        }
    });

    function showPlayerDetailsModal(player) {
        var clubName = window.OBZG_ALL_CLUBS && player.club_id && window.OBZG_ALL_CLUBS[player.club_id] ? window.OBZG_ALL_CLUBS[player.club_id] : '';
        var html = `
        <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Player Details</h5>
                <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <dl class="row mb-0">
                  <dt class="col-sm-4">Name</dt><dd class="col-sm-8">${(player.name || '') + ' ' + (player.surname || '')}</dd>
                  <dt class="col-sm-4">Club</dt><dd class="col-sm-8">${clubName}</dd>
                  <dt class="col-sm-4">City of Birth</dt><dd class="col-sm-8">${player.city_of_birth || ''}</dd>
                  <dt class="col-sm-4">Gender</dt><dd class="col-sm-8">${player.gender || ''}</dd>
                  <dt class="col-sm-4">EMŠO</dt><dd class="col-sm-8">${player.emso || ''}</dd>
                  <dt class="col-sm-4">Address</dt><dd class="col-sm-8">${player.address || ''}</dd>
                  <dt class="col-sm-4">City</dt><dd class="col-sm-8">${player.city || ''}</dd>
                  <dt class="col-sm-4">City Number</dt><dd class="col-sm-8">${player.city_number || ''}</dd>
                  <dt class="col-sm-4">Citizenship</dt><dd class="col-sm-8">${player.citizenship || ''}</dd>
                </dl>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary obzg-modal-cancel">Close</button>
              </div>
            </div>
          </div>
        </div>`;
        closePlayerModal();
        $('body').append(html);
        setTimeout(function(){ $('.obzg-modal').addClass('show'); }, 10);
        $('.obzg-modal-cancel').on('click', closePlayerModal);
    }

    // Add Import from Excel button
    $(function(){
        if($('#obzg-player-admin-root').length) {
            // This button is now rendered by renderPlayerToolbar
        }
    });

    // Show import modal
    $(document).on('click', '#obzg-import-players-btn', function(){
        closePlayerModal();
        var html = `
        <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Import Players from Excel</h5>
                <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
              </div>
              <form id="obzg-import-players-form" enctype="multipart/form-data">
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Select Excel file (.xlsx)</label>
                    <input type="file" class="form-control" name="players_excel" accept=".xlsx" required />
                  </div>
                  <div class="alert alert-info">The Excel file should have columns: Name, Surname, Email, Phone Number, Date of Birth, City of Birth, Gender, EMŠO, Club, Address, City, City Number, Citizenship.</div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Import</button>
                  <button type="button" class="btn btn-secondary obzg-modal-cancel">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>`;
        $('body').append(html);
        setTimeout(function(){ $('.obzg-modal').addClass('show'); }, 10);
        $('.obzg-modal-cancel').on('click', closePlayerModal);
    });

    // Handle Excel import form submit
    $(document).on('submit', '#obzg-import-players-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var formData = new FormData(this);
        formData.append('action', 'obzg_import_players_excel');
        formData.append('_ajax_nonce', OBZG_AJAX_PLAYERS.nonce);
        $form.find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                $form.find('button[type="submit"]').prop('disabled', false);
                if(resp.success) {
                    closePlayerModal();
                    loadPlayers();
                } else {
                    var msg = resp.data && resp.data.message ? resp.data.message : 'Error importing players.';
                    $form.prepend('<div class="alert alert-danger">'+msg+'</div>');
                }
            },
            error: function() {
                $form.find('button[type="submit"]').prop('disabled', false);
                $form.prepend('<div class="alert alert-danger">AJAX error.</div>');
            }
        });
    });

    // Add Club Filter Dropdown
    $(function(){
        if($('#obzg-player-admin-root').length) {
            // This dropdown is now rendered by renderPlayerToolbar
        }
    });

    // Store all loaded players for filtering
    var ALL_OBZG_PLAYERS = [];
    function renderPlayersTable(players) {
        if (!players.length) {
            return '<div class="alert alert-info">No players found.</div>';
        }
        var html = '<table class="table table-striped"><thead><tr>' +
            '<th>Name</th>' +
            '<th>Email</th>' +
            '<th>Phone</th>' +
            '<th>Date of Birth</th>' +
            '<th>Actions</th>' +
            '</tr></thead><tbody>';
        players.forEach(function(player){
            html += `<tr>
                <td>${(player.name || '') + ' ' + (player.surname || '')}</td>
                <td>${player.email || ''}</td>
                <td>${player.number || ''}</td>
                <td>${player.dob || ''}</td>
                <td>
                  <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-primary obzg-edit-player-btn" data-id="${player.id}"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-info obzg-more-player-btn" data-id="${player.id}" title="More"><i class="bi bi-info-circle"></i></button>
                    <button class="btn btn-sm btn-outline-danger obzg-delete-player-btn" data-id="${player.id}" title="Delete"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        return html;
    }
    function loadPlayers() {
        $('#obzg-player-admin-root').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $.ajax({
            url: OBZG_AJAX_PLAYERS.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_player',
                _ajax_nonce: OBZG_AJAX_PLAYERS.nonce
            },
            success: function(resp) {
                if (resp.success && Array.isArray(resp.data)) {
                    ALL_OBZG_PLAYERS = resp.data;
                    // Apply filter if set
                    var clubId = $('#obzg-player-club-filter').val();
                    var filtered = clubId ? ALL_OBZG_PLAYERS.filter(function(p){ return String(p.club_id) === String(clubId); }) : ALL_OBZG_PLAYERS;
                    $('#obzg-player-admin-root').html(renderPlayersTable(filtered));
                } else {
                    $('#obzg-player-admin-root').html('<div class="alert alert-danger">Failed to load players.</div>');
                }
            },
            error: function() {
                $('#obzg-player-admin-root').html('<div class="alert alert-danger">AJAX error loading players.</div>');
            }
        });
    }
    // Club filter change handler
    $(document).on('change', '#obzg-player-club-filter', function(){
        var clubId = $(this).val();
        var filtered = clubId ? ALL_OBZG_PLAYERS.filter(function(p){ return String(p.club_id) === String(clubId); }) : ALL_OBZG_PLAYERS;
        $('#obzg-player-admin-root').html(renderPlayersTable(filtered));
    });
})(jQuery); 