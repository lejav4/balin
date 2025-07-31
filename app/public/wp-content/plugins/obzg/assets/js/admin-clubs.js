(function($){
    // Modal HTML template using Bootstrap 5
    function getModalHtml(club) {
        var isEdit = !!club;
        // Default values for new club
        club = club || {};
        // League options (could be dynamic, here static for now)
        var leagueOptions = [
            'First League',
            'Second League',
            'Third League',
            'Veterans',
            'Women',
            'Youth'
        ];
        var selectedLeagues = Array.isArray(club.league) ? club.league : (club.league ? [club.league] : []);
        return `
            <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">${isEdit ? 'Edit Club' : 'Add Club'}</h5>
                    <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
                  </div>
                  <form id="obzg-club-form">
                    <div class="modal-body">
                      <input type="hidden" name="club_id" value="${isEdit ? club.id : ''}">
                      <div class="mb-3">
                        <label for="obzg-club-title" class="form-label">Club Name</label>
                        <input type="text" class="form-control" id="obzg-club-title" name="club_title" value="${club.title || ''}" required />
                      </div>
                      <div class="mb-3">
                        <label for="obzg-club-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="obzg-club-email" name="club_email" value="${club.email || ''}" />
                      </div>
                      <div class="mb-3">
                        <label for="obzg-club-phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="obzg-club-phone" name="club_phone" value="${club.phone || ''}" />
                      </div>
                      <div class="mb-3">
                        <label for="obzg-club-address" class="form-label">Club Address</label>
                        <input type="text" class="form-control" id="obzg-club-address" name="club_address" value="${club.address || ''}" />
                      </div>
                      <div class="mb-3 row">
                        <div class="col">
                          <label for="obzg-club-city" class="form-label">City</label>
                          <input type="text" class="form-control" id="obzg-club-city" name="club_city" value="${club.city || ''}" />
                        </div>
                        <div class="col">
                          <label for="obzg-club-city-number" class="form-label">City Number</label>
                          <input type="text" class="form-control" id="obzg-club-city-number" name="club_city_number" value="${club.city_number || ''}" />
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="obzg-club-president" class="form-label">President Name</label>
                        <input type="text" class="form-control" id="obzg-club-president" name="club_president" value="${club.president || ''}" />
                      </div>
                      <div class="mb-3">
                        <label for="obzg-club-league" class="form-label fw-bold">League(s)</label>
                        <div class="obzg-league-multiselect position-relative">
                          <div class="obzg-league-pills mb-2"></div>
                          <input type="text" class="form-control obzg-league-input" placeholder="Type to search or click to select..." autocomplete="off" style="background:#fff;cursor:pointer;">
                          <div class="obzg-league-dropdown dropdown-menu w-100 shadow" style="max-height:220px;overflow:auto;">
                            ${leagueOptions.map(opt => `<label class='dropdown-item' data-league-name='${opt.toLowerCase()}'><input type='checkbox' class='obzg-league-checkbox' value="${opt}"${selectedLeagues.includes(opt) ? ' checked' : ''}> ${opt}</label>`).join('')}
                          </div>
                        </div>
                        <div class="form-text text-primary small mt-1">Type to search, click to select, or remove with the x on the pill.</div>
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

    // Render clubs table
    function renderClubsTable(clubs) {
        if (!clubs.length) {
            return '<div class="alert alert-info">No clubs found.</div>';
        }
        var html = '<div class="row g-4">';
        clubs.forEach(function(club){
            var players = Array.isArray(club.players) ? club.players : (club.players ? [club.players] : []);
            var playersHtml = players.length ? players.map(function(p){
                var pid = typeof p === 'object' && p.id ? String(p.id) : String(p);
                var pname = (window.OBZG_ALL_PLAYERS && window.OBZG_ALL_PLAYERS[pid]) ? window.OBZG_ALL_PLAYERS[pid] : `ID ${pid}`;
                return `<span class='badge bg-success me-1 d-inline-flex align-items-center obzg-player-badge' data-club-id='${club.id}' data-player-id='${pid}'>${pname} <button type='button' class='btn-close btn-close-white btn-sm ms-1 obzg-remove-player-btn' title='Remove Player' data-club-id='${club.id}' data-player-id='${pid}' style='font-size:0.7em;'></button></span>`;
            }).join('') : '<span class="text-muted">No players</span>';
            html += `
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-3 text-primary"><i class="bi bi-people-fill me-2"></i>${club.title || ''}</h5>
                  <div class="mb-2"><i class="bi bi-envelope-at me-2 text-secondary"></i><span>${club.email || '<span class=\'text-muted\'>No email</span>'}</span></div>
                  <div class="mb-2"><i class="bi bi-telephone me-2 text-secondary"></i><span>${club.phone || '<span class=\'text-muted\'>No phone</span>'}</span></div>
                  <div class="mb-2"><i class="bi bi-geo-alt me-2 text-secondary"></i><span>${(club.address || '') + (club.city ? ', ' + club.city : '') || '<span class=\'text-muted\'>No address</span>'}</span></div>
                  <div class="mb-2"><strong>Players:</strong> ${playersHtml}</div>
                  <div class="mt-auto pt-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-primary obzg-edit-club-btn" data-id="${club.id}"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-info obzg-more-club-btn" title="More" data-id="${club.id}"><i class="bi bi-info-circle"></i></button>
                    <button class="btn btn-sm btn-success obzg-add-players-btn" title="Add Players" data-id="${club.id}"><i class="bi bi-person-plus"></i></button>
                    <button class="btn btn-sm btn-outline-danger obzg-delete-club-btn" title="Delete" data-id="${club.id}"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>
            `;
        });
        html += '</div>';
        return html;
    }

    // Show club details modal
    function showClubDetailsModal(club) {
        var leagues = Array.isArray(club.league) ? club.league : (club.league ? [club.league] : []);
        var players = Array.isArray(club.players) ? club.players : (club.players ? [club.players] : []);
        var playersHtml = players.length ? players.map(function(p){
            // If player is an object, use .title, else just show ID
            if (typeof p === 'object' && p.title) return `<span class='badge bg-success me-1'>${p.title}</span>`;
            if (window.OBZG_ALL_PLAYERS && window.OBZG_ALL_PLAYERS[p]) return `<span class='badge bg-success me-1'>${window.OBZG_ALL_PLAYERS[p]}</span>`;
            return `<span class='badge bg-secondary me-1'>ID ${p}</span>`;
        }).join('') : '-';
        var html = `
        <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Club Details</h5>
                <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <dl class="row mb-0">
                  <dt class="col-sm-4">Name</dt><dd class="col-sm-8">${club.title || ''}</dd>
                  <dt class="col-sm-4">Email</dt><dd class="col-sm-8">${club.email || ''}</dd>
                  <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">${club.phone || ''}</dd>
                  <dt class="col-sm-4">Address</dt><dd class="col-sm-8">${club.address || ''}</dd>
                  <dt class="col-sm-4">City</dt><dd class="col-sm-8">${club.city || ''}</dd>
                  <dt class="col-sm-4">City Number</dt><dd class="col-sm-8">${club.city_number || ''}</dd>
                  <dt class="col-sm-4">President</dt><dd class="col-sm-8">${club.president || ''}</dd>
                  <dt class="col-sm-4">Leagues</dt><dd class="col-sm-8">${leagues.length ? leagues.map(l => `<span class='badge bg-primary me-1'>${l}</span>`).join('') : '-'}</dd>
                  <dt class="col-sm-4">Players</dt><dd class="col-sm-8">${playersHtml}</dd>
                </dl>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary obzg-modal-cancel">Close</button>
              </div>
            </div>
          </div>
        </div>`;
        closeClubModal();
        $('body').append(html);
        setTimeout(function(){ $('.obzg-modal').addClass('show'); }, 10);
        $('.obzg-modal-cancel').on('click', closeClubModal);
    }

    // Open modal for add/edit
    function openClubModal(club) {
        closeClubModal(); // Ensure only one modal
        var $modal = $(getModalHtml(club));
        $('body').append($modal);
        setTimeout(function(){ $modal.addClass('show'); }, 10); // mimic Bootstrap fade in
        $('.obzg-modal-cancel').on('click', closeClubModal);
        // League multiselect logic
        var $leagueInput = $modal.find('.obzg-league-input');
        var $leagueDropdown = $modal.find('.obzg-league-dropdown');
        var $leaguePills = $modal.find('.obzg-league-pills');
        function updateLeaguePills() {
            var selected = $leagueDropdown.find('input:checked').map(function(){
                var val = $(this).val();
                return {val: val, name: val};
            }).get();
            $leaguePills.html(selected.map(sel => `<span class='badge bg-primary me-1 d-inline-flex align-items-center obzg-league-pill' data-league-val='${sel.val}'>${sel.name} <button type='button' class='btn-close btn-close-white btn-sm ms-1 obzg-remove-league-pill-btn' title='Remove League' data-league-val='${sel.val}' style='font-size:0.7em;'></button></span>`).join(''));
        }
        updateLeaguePills();
        $leagueInput.on('focus click', function(){ $leagueDropdown.show(); });
        $leagueInput.on('input', function(){
            var val = $(this).val().toLowerCase();
            $leagueDropdown.find('.dropdown-item').each(function(){
                var name = $(this).data('league-name');
                if(!val || name.indexOf(val) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        $leagueDropdown.on('mousedown', function(e){ e.preventDefault(); });
        $leagueDropdown.on('change', 'input', function(){ updateLeaguePills(); });
        $leagueDropdown.on('click', 'label', function(){ $leagueInput.focus(); });
        $leaguePills.on('click', '.obzg-remove-league-pill-btn', function(){
            var val = $(this).data('league-val');
            $leagueDropdown.find(`input[value='${val}']`).prop('checked', false).trigger('change');
        });
        $(document).on('mousedown.obzg-league-multiselect', function(e){
            if(!$(e.target).closest('.obzg-league-multiselect').length) $leagueDropdown.hide();
        });
        $modal.on('hidden.bs.modal', function(){ $(document).off('mousedown.obzg-league-multiselect'); });
        // On submit, collect selected leagues as array
        $('#obzg-club-form').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = {
                action: 'obzg_save_club',
                _ajax_nonce: OBZG_AJAX.nonce,
                club_id: $form.find('[name="club_id"]').val(),
                club_title: $form.find('[name="club_title"]').val(),
                club_desc: $form.find('[name="club_desc"]').val(),
                club_email: $form.find('[name="club_email"]').val(),
                club_phone: $form.find('[name="club_phone"]').val(),
                club_address: $form.find('[name="club_address"]').val(),
                club_city: $form.find('[name="club_city"]').val(),
                club_city_number: $form.find('[name="club_city_number"]').val(),
                club_president: $form.find('[name="club_president"]').val(),
                club_league: $leagueDropdown.find('input:checked').map(function(){return $(this).val();}).get()
            };
            $form.find('button[type="submit"]').prop('disabled', true);
            $.post(OBZG_AJAX.ajax_url, data, function(resp){
                $form.find('button[type="submit"]').prop('disabled', false);
                if(resp.success) {
                    closeClubModal();
                    loadClubs();
                } else {
                    var msg = resp.data && resp.data.message ? resp.data.message : 'Error saving club.';
                    $form.prepend('<div class="alert alert-danger">'+msg+'</div>');
                }
            }).fail(function(){
                $form.find('button[type="submit"]').prop('disabled', false);
                $form.prepend('<div class="alert alert-danger">AJAX error.</div>');
            });
        });
    }

    function closeClubModal() {
        $('.obzg-modal').remove();
    }

    // Load clubs list
    function loadClubs() {
        $('#obzg-club-admin-root').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $.ajax({
            url: OBZG_AJAX.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_club',
                _ajax_nonce: OBZG_AJAX.nonce
            },
            success: function(resp) {
                if (resp.success && Array.isArray(resp.data)) {
                    $('#obzg-club-admin-root').html(renderClubsTable(resp.data));
                } else {
                    $('#obzg-club-admin-root').html('<div class="alert alert-danger">Failed to load clubs.</div>');
                }
            },
            error: function() {
                $('#obzg-club-admin-root').html('<div class="alert alert-danger">AJAX error loading clubs.</div>');
            }
        });
    }

    // Add button handler
    $(document).on('click', '#obzg-add-club-btn', function(){
        openClubModal();
    });

    // Edit button handler
    $(document).on('click', '.obzg-edit-club-btn', function(){
        var clubId = $(this).data('id');
        // Fetch club data via AJAX
        $.ajax({
            url: OBZG_AJAX.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_single_club',
                _ajax_nonce: OBZG_AJAX.nonce,
                club_id: clubId
            },
            success: function(resp) {
                if (resp.success && resp.data) {
                    openClubModal(resp.data);
                } else {
                    alert('Failed to load club data.');
                }
            },
            error: function() {
                alert('AJAX error loading club data.');
            }
        });
    });

    // More button handler
    $(document).on('click', '.obzg-more-club-btn', function(){
        var clubId = $(this).data('id');
        // Use the same AJAX as edit to get all details
        $.ajax({
            url: OBZG_AJAX.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_single_club',
                _ajax_nonce: OBZG_AJAX.nonce,
                club_id: clubId
            },
            success: function(resp) {
                if (resp.success && resp.data) {
                    showClubDetailsModal(resp.data);
                } else {
                    alert('Failed to load club data.');
                }
            },
            error: function() {
                alert('AJAX error loading club data.');
            }
        });
    });

    // Add Players button handler
    $(document).on('click', '.obzg-add-players-btn', function(){
        var clubId = $(this).data('id');
        // Fetch all players and current club players
        $.when(
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: { action: 'obzg_get_player', _ajax_nonce: OBZG_AJAX.nonce }
            }),
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: { action: 'obzg_get_single_club', _ajax_nonce: OBZG_AJAX.nonce, club_id: clubId }
            })
        ).done(function(playersResp, clubResp) {
            var players = (playersResp[0].success && Array.isArray(playersResp[0].data)) ? playersResp[0].data : [];
            var club = (clubResp[0].success && clubResp[0].data) ? clubResp[0].data : {};
            var assigned = Array.isArray(club.players) ? club.players.map(String) : (club.players ? [String(club.players)] : []);
            var html = `
            <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Assign Players to ${club.title || 'Club'}</h5>
                    <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
                  </div>
                  <form id="obzg-assign-players-form">
                    <div class="modal-body">
                      <input type="hidden" name="club_id" value="${clubId}">
                      <div class="mb-3">
                        <label class="form-label">Players</label>
                        <div class="obzg-player-multiselect position-relative">
                          <div class="obzg-player-pills mb-2"></div>
                          <input type="text" class="form-control obzg-player-input" placeholder="Type to search or click to select..." autocomplete="off" style="background:#fff;cursor:pointer;">
                          <div class="obzg-player-dropdown dropdown-menu w-100 shadow" style="max-height:220px;overflow:auto;">
                            ${players.map(p => {
                                var pname = (p.name || '') + ' ' + (p.surname || '');
                                return `<label class='dropdown-item' data-player-name='${pname.toLowerCase()}'><input type='checkbox' class='obzg-player-checkbox' value='${p.id}'${assigned.includes(String(p.id)) ? ' checked' : ''}> ${pname}</label>`;
                            }).join('')}
                          </div>
                        </div>
                        <div class="form-text text-success small mt-1">Type to search, click to select, or remove with the x on the pill.</div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success">Save</button>
                      <button type="button" class="btn btn-secondary obzg-modal-cancel">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>`;
            closeClubModal();
            $('body').append(html);
            setTimeout(function(){ $('.obzg-modal').addClass('show'); }, 10);
            $('.obzg-modal-cancel').on('click', closeClubModal);

            // Pills and dropdown logic
            var $modal = $('.obzg-modal');
            var $input = $modal.find('.obzg-player-input');
            var $dropdown = $modal.find('.obzg-player-dropdown');
            var $pills = $modal.find('.obzg-player-pills');
            function updatePills() {
                var selected = $dropdown.find('input:checked').map(function(){
                    var pid = $(this).val();
                    var p = players.find(p => String(p.id) === String(pid));
                    var pname = p ? ((p.name || '') + ' ' + (p.surname || '')) : pid;
                    return {id: pid, name: pname};
                }).get();
                $pills.html(selected.map(sel => `<span class='badge bg-success me-1 d-inline-flex align-items-center obzg-player-pill' data-player-id='${sel.id}'>${sel.name} <button type='button' class='btn-close btn-close-white btn-sm ms-1 obzg-remove-player-pill-btn' title='Remove Player' data-player-id='${sel.id}' style='font-size:0.7em;'></button></span>`).join(''));
            }
            updatePills();
            $input.on('focus click', function(){ $dropdown.show(); });
            $input.on('input', function(){
                var val = $(this).val().toLowerCase();
                $dropdown.find('.dropdown-item').each(function(){
                    var name = $(this).data('player-name');
                    if(!val || name.indexOf(val) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            $dropdown.on('mousedown', function(e){ e.preventDefault(); });
            $dropdown.on('change', 'input', function(){ updatePills(); });
            $dropdown.on('click', 'label', function(){ $input.focus(); });
            $pills.on('click', '.obzg-remove-player-pill-btn', function(){
                var pid = $(this).data('player-id');
                $dropdown.find(`input[value='${pid}']`).prop('checked', false).trigger('change');
            });
            $(document).on('mousedown.obzg-multiselect', function(e){
                if(!$(e.target).closest('.obzg-player-multiselect').length) $dropdown.hide();
            });
            $modal.on('hidden.bs.modal', function(){ $(document).off('mousedown.obzg-multiselect'); });
            // On submit
            $('#obzg-assign-players-form').on('submit', function(e){
                e.preventDefault();
                var $form = $(this);
                var data = {
                    action: 'obzg_assign_players',
                    _ajax_nonce: OBZG_AJAX.nonce,
                    club_id: clubId,
                    club_players: $dropdown.find('input:checked').map(function(){return $(this).val();}).get()
                };
                $form.find('button[type="submit"]').prop('disabled', true);
                $.post(OBZG_AJAX.ajax_url, data, function(resp){
                    $form.find('button[type="submit"]').prop('disabled', false);
                    if(resp.success) {
                        closeClubModal();
                        loadClubs();
                    } else {
                        $form.prepend('<div class="alert alert-danger">Failed to assign players.</div>');
                    }
                }).fail(function(){
                    $form.find('button[type="submit"]').prop('disabled', false);
                    $form.prepend('<div class="alert alert-danger">AJAX error.</div>');
                });
            });
        });
    });

    // Delete button handler
    $(document).on('click', '.obzg-delete-club-btn', function(e){
        e.preventDefault();
        var clubId = $(this).data('id');
        if(confirm('Are you sure you want to delete this club?')) {
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: {
                    action: 'obzg_delete_club',
                    _ajax_nonce: OBZG_AJAX.nonce,
                    club_id: clubId
                },
                success: function(resp) {
                    if(resp.success) {
                        loadClubs();
                    } else {
                        alert('Failed to delete club.');
                    }
                },
                error: function() {
                    alert('AJAX error deleting club.');
                }
            });
        }
    });

    // Remove player from club handler
    $(document).on('click', '.obzg-remove-player-btn', function(e){
        e.preventDefault();
        var clubId = $(this).data('club-id');
        var playerId = $(this).data('player-id');
        if(confirm('Remove this player from the club?')) {
            // Get current players for the club
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: { action: 'obzg_get_single_club', _ajax_nonce: OBZG_AJAX.nonce, club_id: clubId },
                success: function(resp) {
                    if(resp.success && resp.data) {
                        var players = Array.isArray(resp.data.players) ? resp.data.players : (resp.data.players ? [resp.data.players] : []);
                        var newPlayers = players.filter(function(pid){ return String(pid) !== String(playerId); });
                        // Save updated players
                        $.post(OBZG_AJAX.ajax_url, {
                            action: 'obzg_assign_players',
                            _ajax_nonce: OBZG_AJAX.nonce,
                            club_id: clubId,
                            club_players: newPlayers
                        }, function(saveResp){
                            if(saveResp.success) {
                                loadClubs();
                            } else {
                                alert('Failed to remove player.');
                            }
                        });
                    }
                },
                error: function(){ alert('AJAX error removing player.'); }
            });
        }
    });

    // Add sample clubs button handler
    $(document).on('click', '#add-sample-clubs-btn', function(){
        if(confirm('This will add 16 sample bocce clubs. Continue?')) {
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: {
                    action: 'obzg_add_sample_clubs',
                    _ajax_nonce: OBZG_AJAX.nonce
                },
                success: function(resp) {
                    if(resp.success) {
                        alert(resp.data.message);
                        loadClubs();
                    } else {
                        alert('Error: ' + (resp.data && resp.data.message ? resp.data.message : 'Failed to add sample clubs.'));
                    }
                },
                error: function() {
                    alert('AJAX error adding sample clubs.');
                }
            });
        }
    });

    // Import from Excel button handler
    $(document).on('click', '#obzg-import-clubs-btn', function(){
        closeClubModal();
        var html = `
        <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Import Clubs from Excel</h5>
                <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
              </div>
              <form id="obzg-import-clubs-form" enctype="multipart/form-data">
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Select Excel file (.xlsx)</label>
                    <input type="file" class="form-control" name="clubs_excel" accept=".xlsx" required />
                  </div>
                  <div class="alert alert-info">The Excel file should have columns: Name, Email, Phone, Address, City, City Number, President, League(s).</div>
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
        $('.obzg-modal-cancel').on('click', closeClubModal);
    });

    // On page load, fetch all players and store in window.OBZG_ALL_PLAYERS for name lookup
    $(function(){
        if($('#obzg-club-admin-root').length) {
            $.ajax({
                url: OBZG_AJAX.ajax_url,
                method: 'POST',
                data: { action: 'obzg_get_player', _ajax_nonce: OBZG_AJAX.nonce },
                success: function(resp) {
                    if(resp.success && Array.isArray(resp.data)) {
                        window.OBZG_ALL_PLAYERS = {};
                        resp.data.forEach(function(p){ window.OBZG_ALL_PLAYERS[p.id] = (p.name || '') + ' ' + (p.surname || ''); });
                    }
                }
            });
            loadClubs();
            // Add button
            if ($('.obzg-club-toolbar').length === 0) {
                $('#obzg-club-admin-root').before('<div class="obzg-club-toolbar"><button id="obzg-add-club-btn" class="btn btn-success"><i class="bi bi-plus-lg"></i> <span class="ms-1">Add Club</span></button> <button id="obzg-import-clubs-btn" class="btn btn-secondary"><i class="bi bi-upload"></i> <span class="ms-1">Import from Excel</span></button> <button id="add-sample-clubs-btn" class="btn btn-info"><i class="bi bi-database-add"></i> <span class="ms-1">Add 16 Sample Clubs</span></button></div>');
            }
        }
    });
})(jQuery); 