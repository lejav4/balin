jQuery(document).ready(function($) {
    let tournaments = [];
    let clubs = [];
    let matches = [];
    let editingTournament = null;
    let currentTournament = null;

    // Initialize the application
    function init() {
        renderTournamentInterface();
        loadTournaments();
        loadClubs();
        loadMatches();
        setupEventListeners();
    }

    // Render the main tournament interface
    function renderTournamentInterface() {
        const container = $('#obzg-tournament-admin-root');
        container.html(`
            <div class="tournament-header">
                <h1><i class="bi bi-trophy"></i> Tournament Management</h1>
                <p>Manage your bocce ball tournaments, schedules, and participants</p>
            </div>
            
            <div id="alerts-container"></div>
            
            <div class="tournament-actions">
                <button id="add-tournament-btn" class="btn btn-add-tournament">
                    <i class="bi bi-plus-circle"></i> Add New Tournament
                </button>
            </div>
            
            <div id="tournaments-list">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            
            <!-- Tournament Modal -->
            <div class="modal fade" id="tournament-modal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Tournament</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="tournament-form">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-title" class="form-label">Tournament Name *</label>
                                            <input type="text" class="form-control" id="tournament-title" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-status" class="form-label">Status</label>
                                            <select class="form-select" id="tournament-status">
                                                <option value="">Select Status</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Upcoming">Upcoming</option>
                                                <option value="Active">Active</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-start-date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="tournament-start-date">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-end-date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="tournament-end-date">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tournament-location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="tournament-location" placeholder="Tournament venue">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tournament-desc" class="form-label">Description</label>
                                    <textarea class="form-control" id="tournament-desc" rows="3" placeholder="Tournament description and details"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tournament-type" class="form-label">Tournament Type</label>
                                            <select class="form-select" id="tournament-type">
                                                <option value="standard">Standard Tournament</option>
                                                <option value="swiss">Swiss System Tournament</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tournament-max-teams" class="form-label">Maximum Teams</label>
                                            <input type="number" class="form-control" id="tournament-max-teams" min="0" value="0">
                                            <div class="form-text">0 = unlimited teams</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tournament-num-rounds" class="form-label">Number of Rounds (Swiss)</label>
                                            <input type="number" class="form-control" id="tournament-num-rounds" min="1" max="10" value="5">
                                            <div class="form-text">Only applies to Swiss system tournaments</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-clubs" class="form-label">Participating Clubs</label>
                                            <select class="form-select" id="tournament-clubs" multiple>
                                                <option value="">Loading clubs...</option>
                                            </select>
                                            <div class="form-text">Hold Ctrl/Cmd to select multiple clubs</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tournament-matches" class="form-label">Tournament Matches</label>
                                            <select class="form-select" id="tournament-matches" multiple>
                                                <option value="">Loading matches...</option>
                                            </select>
                                            <div class="form-text">Hold Ctrl/Cmd to select multiple matches</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="cancel-tournament-btn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Tournament</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `);
    }

    // Load tournaments from server
    function loadTournaments() {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_get_tournament',
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    tournaments = response.data;
                    renderTournamentsList();
                } else {
                    showAlert('Error loading tournaments: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to load tournaments', 'danger');
            }
        });
    }

    // Load clubs for dropdown
    function loadClubs() {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_get_club',
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    clubs = response.data;
                    updateClubsDropdown();
                }
            }
        });
    }

    // Load matches for dropdown
    function loadMatches() {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_get_match',
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    matches = response.data;
                    updateMatchesDropdown();
                }
            }
        });
    }

    // Setup event listeners
    function setupEventListeners() {
        // Add tournament button
        $(document).on('click', '#add-tournament-btn', function() {
            showTournamentForm();
        });

        // Save tournament form
        $(document).on('submit', '#tournament-form', function(e) {
            e.preventDefault();
            saveTournament();
        });

        // Cancel button
        $(document).on('click', '#cancel-tournament-btn', function() {
            hideTournamentForm();
        });

        // Edit tournament
        $(document).on('click', '.edit-tournament-btn', function() {
            const tournamentId = $(this).data('id');
            editTournament(tournamentId);
        });

        // Delete tournament
        $(document).on('click', '.delete-tournament-btn', function() {
            const tournamentId = $(this).data('id');
            deleteTournament(tournamentId);
        });

        // Swiss system buttons
        $(document).on('click', '.manage-swiss-btn', function() {
            const tournamentId = $(this).data('id');
            showSwissManagement(tournamentId);
        });

        $(document).on('click', '.generate-next-round-btn', function() {
            generateNextRound();
        });

        $(document).on('click', '.enter-result-btn', function() {
            const matchId = $(this).data('match-id');
            const club1 = $(this).data('club1');
            const club2 = $(this).data('club2');
            
            console.log('Enter result clicked:', { matchId, club1, club2 });
            
            $('#club1-name').text(club1);
            $('#club2-name').text(club2);
            $('#club1-score').val(0);
            $('#club2-score').val(0);
            $('#confirm-save-result').data('match-id', matchId);
            
            // Use Bootstrap 5 modal API
            const modalElement = document.getElementById('match-result-modal');
            console.log('Modal element:', modalElement);
            
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element not found!');
                alert('Error: Modal not found. Please refresh the page.');
            }
        });

        $(document).on('click', '#confirm-save-result', function() {
            const matchId = $(this).data('match-id');
            console.log('Save result clicked, matchId:', matchId);
            saveMatchResult(matchId);
        });

        // Close modal when clicking cancel
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            const modalElement = $(this).closest('.modal');
            const modal = bootstrap.Modal.getInstance(modalElement[0]);
            if (modal) {
                modal.hide();
            }
        });

        // Team management buttons
        $(document).on('click', '.manage-teams-btn', function() {
            const tournamentId = $(this).data('id');
            showTeamManagement(tournamentId);
        });

        $(document).on('click', '.add-team-btn', function() {
            const teamId = $(this).data('team-id');
            addTeamToTournament(teamId);
        });

        $(document).on('click', '.remove-team-btn', function() {
            const teamId = $(this).data('team-id');
            removeTeamFromTournament(teamId);
        });

        $(document).on('click', '.add-random-teams-btn', function() {
            const numTeams = $('#random-teams-count').val() || 16;
            addRandomTeams(numTeams);
        });

        // Close alert
        $(document).on('click', '.alert .btn-close', function() {
            $(this).closest('.alert').remove();
        });

        // Back to tournaments button
        $(document).off('click.obzg-back-to-tournaments').on('click.obzg-back-to-tournaments', '.obzg-back-to-tournaments-btn', function(e) {
            e.preventDefault();
            showTournamentList();
        });
    }

    // Render tournaments list
    function renderTournamentsList() {
        const container = $('#tournaments-list');
        if (tournaments.length === 0) {
            container.html('<div class="text-center text-muted py-4">No tournaments found</div>');
            return;
        }

        let html = '<div class="row">';
        tournaments.forEach(function(tournament) {
            const statusClass = getStatusClass(tournament.status);
            const statusText = tournament.status || 'Draft';
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${tournament.title}</h5>
                                <div>
                                    <span class="badge ${statusClass}">${statusText}</span>
                                    ${tournament.tournament_type === 'swiss' ? '<span class="badge bg-info ms-1">Swiss</span>' : ''}
                                </div>
                            </div>
                            <p class="card-text text-muted small">${tournament.desc || 'No description'}</p>
                            <div class="row text-muted small mb-3">
                                <div class="col-6">
                                    <i class="bi bi-calendar-event"></i> ${tournament.start_date || 'TBD'}
                                </div>
                                <div class="col-6">
                                    <i class="bi bi-geo-alt"></i> ${tournament.location || 'TBD'}
                                </div>
                            </div>
                                                                                        <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    ${tournament.clubs ? tournament.clubs.length : 0}${tournament.max_teams > 0 ? '/' + tournament.max_teams : ''} teams, 
                                    ${tournament.matches ? tournament.matches.length : 0} matches
                                </small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info manage-teams-btn" data-id="${tournament.id}" title="Manage Teams">
                                            <i class="bi bi-people"></i>
                                        </button>
                                        <button class="btn btn-outline-success manage-swiss-btn" data-id="${tournament.id}" title="Manage Swiss System">
                                            <i class="bi bi-trophy"></i>
                                        </button>
                                        <button class="btn btn-outline-primary edit-tournament-btn" data-id="${tournament.id}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-tournament-btn" data-id="${tournament.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }

    // Get status class for badge
    function getStatusClass(status) {
        switch(status) {
            case 'Active': return 'bg-success';
            case 'Upcoming': return 'bg-primary';
            case 'Completed': return 'bg-secondary';
            case 'Cancelled': return 'bg-danger';
            default: return 'bg-warning';
        }
    }

    // Show tournament form
    function showTournamentForm(editingTournament = null) {
        const modal = $('#tournament-modal');
        let title = editingTournament ? 'Edit Tournament' : 'Add New Tournament';
        let tournament = editingTournament || {};
        // Only keep the required fields
        const {
            title: tTitle = '',
            status = '',
            start_date = '',
            end_date = '',
            location = '',
            desc = '',
            tournament_type = 'swiss',
            max_teams = '',
            num_rounds = ''
        } = tournament;
        
        const formHtml = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tournament-form">
                    <div class="modal-body bg-light">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Tournament Name</label>
                                        <input type="text" class="form-control" id="tournament-title" value="${tTitle || ''}" required />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Status</label>
                                        <select class="form-select" id="tournament-status">
                                            <option value="">Select status</option>
                                            <option value="draft" ${status === 'draft' ? 'selected' : ''}>Draft</option>
                                            <option value="active" ${status === 'active' ? 'selected' : ''}>Active</option>
                                            <option value="completed" ${status === 'completed' ? 'selected' : ''}>Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Start Date</label>
                                        <input type="date" class="form-control" id="tournament-start-date" value="${start_date || ''}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">End Date</label>
                                        <input type="date" class="form-control" id="tournament-end-date" value="${end_date || ''}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Location</label>
                                        <input type="text" class="form-control" id="tournament-location" value="${location || ''}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tournament Type</label>
                                        <select class="form-select" id="tournament-type">
                                            <option value="swiss" ${tournament_type === 'swiss' ? 'selected' : ''}>Swiss</option>
                                            <option value="single_elimination" ${tournament_type === 'single_elimination' ? 'selected' : ''}>Single Elimination</option>
                                            <option value="double_elimination" ${tournament_type === 'double_elimination' ? 'selected' : ''}>Double Elimination</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="tournament-desc" rows="2">${desc || ''}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Maximum Teams</label>
                                        <input type="number" class="form-control" id="tournament-max-teams" min="2" value="${max_teams || ''}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Number of Rounds</label>
                                        <input type="number" class="form-control" id="tournament-num-rounds" min="1" value="${num_rounds || ''}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">${editingTournament ? 'Save Changes' : 'Create Tournament'}</button>
                    </div>
                </form>
            </div>
        </div>`;
        modal.html(formHtml).modal('show');
    }

    // Hide tournament form
    function hideTournamentForm() {
        $('#tournament-modal').modal('hide');
        editingTournament = null;
    }

    // Edit tournament
    function editTournament(tournamentId) {
        const tournament = tournaments.find(t => t.id == tournamentId);
        if (!tournament) {
            showAlert('Tournament not found', 'danger');
            return;
        }

        editingTournament = tournament;
        showTournamentForm(tournament);
    }

    // Save tournament
    function saveTournament() {
        const formData = new FormData();
        formData.append('action', 'obzg_save_tournament');
        formData.append('_ajax_nonce', OBZG_AJAX_TOURNAMENTS.nonce);
        
        if (editingTournament) {
            formData.append('tournament_id', editingTournament.id);
        }
        
        formData.append('tournament_title', $('#tournament-title').val());
        formData.append('tournament_desc', $('#tournament-desc').val());
        formData.append('tournament_start_date', $('#tournament-start-date').val());
        formData.append('tournament_end_date', $('#tournament-end-date').val());
        formData.append('tournament_location', $('#tournament-location').val());
        formData.append('tournament_status', $('#tournament-status').val());
        formData.append('tournament_type', $('#tournament-type').val());
        formData.append('tournament_max_teams', $('#tournament-max-teams').val());
        formData.append('tournament_num_rounds', $('#tournament-num-rounds').val());
        
        // Add selected clubs
        const selectedClubs = $('#tournament-clubs').val();
        if (selectedClubs) {
            selectedClubs.forEach(function(clubId) {
                formData.append('tournament_clubs[]', clubId);
            });
        }
        
        // Add selected matches
        const selectedMatches = $('#tournament-matches').val();
        if (selectedMatches) {
            selectedMatches.forEach(function(matchId) {
                formData.append('tournament_matches[]', matchId);
            });
        }

        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Tournament saved successfully!', 'success');
                    hideTournamentForm();
                    loadTournaments();
                } else {
                    showAlert('Error saving tournament: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to save tournament', 'danger');
            }
        });
    }

    // Delete tournament
    function deleteTournament(tournamentId) {
        if (!confirm('Are you sure you want to delete this tournament?')) {
            return;
        }

        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_delete_tournament',
                tournament_id: tournamentId,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Tournament deleted successfully!', 'success');
                    loadTournaments();
                } else {
                    showAlert('Error deleting tournament: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to delete tournament', 'danger');
            }
        });
    }

    // Update clubs dropdown
    function updateClubsDropdown() {
        const select = $('#tournament-clubs');
        select.empty();
        select.append('<option value="">Select clubs...</option>');
        
        clubs.forEach(function(club) {
            select.append(`<option value="${club.id}">${club.title}</option>`);
        });
    }

    // Update matches dropdown
    function updateMatchesDropdown() {
        const select = $('#tournament-matches');
        select.empty();
        select.append('<option value="">Select matches...</option>');
        
        matches.forEach(function(match) {
            select.append(`<option value="${match.id}">${match.title}</option>`);
        });
    }

    // Show alert
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alerts-container').append(alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('#alerts-container .alert').first().remove();
        }, 5000);
    }

    // Swiss System Management
    function showSwissManagement(tournamentId) {
        const tournament = tournaments.find(t => t.id == tournamentId);
        if (!tournament) {
            showAlert('Tournament not found', 'danger');
            return;
        }

        currentTournament = tournament;
        renderSwissInterface();
        loadTournamentStandings(tournamentId);
    }

    function renderSwissInterface() {
        const container = $('#obzg-tournament-admin-root');
        const currentTeams = currentTournament.clubs ? currentTournament.clubs.length : 0;
        const maxTeams = currentTournament.max_teams || 0;
        const teamLimitText = maxTeams > 0 ? `${currentTeams}/${maxTeams} teams` : `${currentTeams} teams (unlimited)`;
        // Calculate optimal rounds
        const optimalRounds = Math.ceil(Math.log2(currentTeams));

        container.html(`
            <div class="tournament-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="bi bi-trophy"></i> Swiss System Management</h1>
                        <p>${currentTournament.title} - ${teamLimitText}</p>
                    </div>
                    <button class="btn btn-outline-light obzg-back-to-tournaments-btn">
                        <i class="bi bi-arrow-left"></i> Back to Tournaments
                    </button>
                </div>
            </div>
            
            <div id="alerts-container"></div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-gear"></i> Generate Next Round</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="next-round-number" class="form-label">Round Number</label>
                                    <input type="number" class="form-control" id="next-round-number" min="1" max="${optimalRounds}" value="1">
                                    <div class="form-text text-info">Maximum rounds for ${currentTournament.clubs.length} teams: <b>${optimalRounds}</b></div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary generate-next-round-btn">
                                        <i class="bi bi-play-circle"></i> Generate Next Round
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Generate rounds one by one based on current results</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Tournament Rounds</h5>
                        </div>
                        <div class="card-body">
                            <div id="rounds-container">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                    <p>No rounds generated yet. Click "Generate Next Round" to start.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-list-ol"></i> Standings</h5>
                        </div>
                        <div class="card-body">
                            <div id="standings-container">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-list" style="font-size: 2rem;"></i>
                                    <p>Standings will appear here</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            
            <!-- Match Result Modal -->
            <div class="modal fade" id="match-result-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Enter Match Result</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-5">
                                    <label class="form-label" id="club1-name">Club 1</label>
                                    <input type="number" class="form-control" id="club1-score" min="0" value="0">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="pt-2">vs</div>
                                </div>
                                <div class="col-5">
                                    <label class="form-label" id="club2-name">Club 2</label>
                                    <input type="number" class="form-control" id="club2-score" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirm-save-result">Save Result</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        // Load current standings and rounds
        loadTournamentStandings(currentTournament.id);

        // Add frontend validation for round number and results completeness
        $(document).off('click.swiss-round-limit').on('click.swiss-round-limit', '.generate-next-round-btn', function(e) {
            const roundNumber = parseInt($('#next-round-number').val(), 10);
            // Check for round limit
            const optimalRounds = Math.ceil(Math.log2(currentTournament.clubs ? currentTournament.clubs.length : 0));
            if (roundNumber > optimalRounds) {
                showAlert(`You cannot generate more than ${optimalRounds} rounds for ${currentTournament.clubs.length} teams.`, 'warning');
                $('#next-round-number').val(optimalRounds);
                e.preventDefault();
                return false;
            }
            // Check if all results for the latest round are entered
            if (currentTournament.rounds && currentTournament.rounds.length > 0) {
                const latestRound = currentTournament.rounds[currentTournament.rounds.length - 1];
                if (latestRound && latestRound.matches) {
                    const incomplete = latestRound.matches.some(match => {
                        // Only check real matches, not BYE
                        if (match.club2_id === 0) return false;
                        return !(match.result && match.result.club1_score !== undefined && match.result.club2_score !== undefined);
                    });
                    if (incomplete) {
                        showAlert(`Please enter all results for round ${latestRound.round} before generating the next round.`, 'danger');
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });

        // Add handler for back button
        $(document).off('click.obzg-back-to-tournaments').on('click.obzg-back-to-tournaments', '.obzg-back-to-tournaments-btn', function(e) {
            e.preventDefault();
            showTournamentList();
        });
    }

    function showTournamentList() {
        currentTournament = null;
        renderTournamentInterface();
        loadTournaments();
    }

    function loadTournamentStandings(tournamentId) {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_get_tournament_standings',
                tournament_id: tournamentId,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderStandings(response.data.standings);
                    renderRounds(response.data.rounds);
                } else {
                    showAlert('Error loading tournament data: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to load tournament data', 'danger');
            }
        });
    }

    function renderStandings(standings) {
        const container = $('#standings-container');
        if (!standings || standings.length === 0) {
            container.html('<div class="text-center text-muted py-4">No standings available</div>');
            return;
        }

        // Sort by points (descending)
        standings.sort((a, b) => b.points - a.points);

        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Pos</th><th>Club</th><th>Pts</th><th>W</th><th>D</th><th>L</th></tr></thead><tbody>';
        
        standings.forEach((standing, index) => {
            const position = index + 1;
            const positionClass = position <= 3 ? 'fw-bold text-primary' : '';
            html += `
                <tr class="${positionClass}">
                    <td>${position}</td>
                    <td>${standing.club_name}</td>
                    <td class="fw-bold">${standing.points}</td>
                    <td class="text-success">${standing.wins}</td>
                    <td class="text-warning">${standing.draws}</td>
                    <td class="text-danger">${standing.losses}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        html += '<div class="d-flex justify-content-center"><button class="btn btn-outline-primary btn-sm mt-2" id="show-standings-details"><i class="bi bi-list"></i> Show Details</button></div>';
        container.html(html);

        // Modal for details (append to body if not present)
        if ($('#standings-details-modal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="standings-details-modal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Standings Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="standings-details-content">
                                <!-- Details will be filled dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        // Button click handler
        $('#show-standings-details').off('click').on('click', function() {
            // Build summary table with Points Difference for each round and total PDiff
            let detailsHtml = '';
            if (!currentTournament || !currentTournament.rounds) {
                detailsHtml = '<div class="text-muted">No round data available.</div>';
            } else {
                // Prepare data for all teams
                const standings = (currentTournament.standings || []).slice().sort((a, b) => b.points - a.points);
                const teams = standings.map(s => ({
                    id: s.club_id,
                    name: s.club_name,
                    points: s.points,
                    wins: s.wins,
                    draws: s.draws,
                    losses: s.losses,
                    rounds: [], // will fill below
                    PDiff: 0, // total points difference
                    Buc1: 0, // placeholder
                    BucT: 0 // placeholder
                }));
                // Map club_id to index for position lookup
                const clubIdToIndex = {};
                teams.forEach((t, i) => { clubIdToIndex[t.id] = i; });
                // Fill rounds results (Points Difference)
                currentTournament.rounds.forEach((round, rIdx) => {
                    round.matches.forEach(match => {
                        // For both clubs
                        [
                            { id: match.club1_id, oppId: match.club2_id, score: match.result ? match.result.club1_score : null, oppScore: match.result ? match.result.club2_score : null },
                            { id: match.club2_id, oppId: match.club1_id, score: match.result ? match.result.club2_score : null, oppScore: match.result ? match.result.club1_score : null }
                        ].forEach(({ id, oppId, score, oppScore }) => {
                            if (!id || !clubIdToIndex.hasOwnProperty(id)) return;
                            let res = '';
                            let diff = 0;
                            if (oppId === 0) {
                                res = 'BYE';
                            } else if (score !== null && oppScore !== null) {
                                diff = score - oppScore;
                                if (diff > 0) res = `+${diff}`;
                                else if (diff < 0) res = `${diff}`;
                                else res = '0';
                                teams[clubIdToIndex[id]].PDiff += diff;
                            } else {
                                res = '';
                            }
                            teams[clubIdToIndex[id]].rounds[rIdx] = res;
                        });
                    });
                });
                // Calculate tiebreakers (placeholders or simple Buchholz)
                teams.forEach((team, idx) => {
                    // Buchholz: sum of opponents' points
                    let buc1 = 0, buct = 0;
                    team.rounds.forEach((res, rIdx) => {
                        const round = currentTournament.rounds[rIdx];
                        if (!round) return;
                        let oppId = null;
                        round.matches.forEach(match => {
                            if (match.club1_id === team.id) oppId = match.club2_id;
                            else if (match.club2_id === team.id) oppId = match.club1_id;
                        });
                        if (oppId && clubIdToIndex[oppId] !== undefined) {
                            buc1 += teams[clubIdToIndex[oppId]].points;
                            buct += teams[clubIdToIndex[oppId]].points;
                        }
                    });
                    team.Buc1 = buc1;
                    team.BucT = buct;
                });
                // Calculate positions with ties
                let posArr = [];
                let lastPts = null, lastPos = 1, tieCount = 0;
                teams.forEach((t, i) => {
                    if (lastPts === t.points) {
                        tieCount++;
                        posArr.push(`${lastPos}-${lastPos+tieCount}`);
                    } else {
                        lastPos = i+1;
                        tieCount = 0;
                        posArr.push(`${lastPos}`);
                    }
                    lastPts = t.points;
                });
                // Table header
                detailsHtml += '<div class="table-responsive"><table class="table table-bordered table-sm mb-0">';
                detailsHtml += '<thead><tr><th>Pos</th><th>Name</th><th>Points</th>';
                for (let r = 0; r < (currentTournament.rounds ? currentTournament.rounds.length : 0); r++) {
                    detailsHtml += `<th>Round #${r+1}</th>`;
                }
                detailsHtml += '<th>PDiff</th><th>Buc1</th><th>BucT</th></tr></thead><tbody>';
                // Table rows
                teams.forEach((team, i) => {
                    detailsHtml += `<tr><td>${posArr[i]}</td><td>${team.name}</td><td class="fw-bold">${team.points}</td>`;
                    for (let r = 0; r < (currentTournament.rounds ? currentTournament.rounds.length : 0); r++) {
                        detailsHtml += `<td>${team.rounds[r] || ''}</td>`;
                    }
                    detailsHtml += `<td>${team.PDiff}</td><td>${team.Buc1}</td><td>${team.BucT}</td></tr>`;
                });
                detailsHtml += '</tbody></table></div>';
            }
            $('#standings-details-content').html(detailsHtml);
            const modal = new bootstrap.Modal(document.getElementById('standings-details-modal'));
            modal.show();
        });
    }

    function renderRounds(rounds) {
        const container = $('#rounds-container');
        if (!rounds || rounds.length === 0) {
            container.html('<div class="text-center text-muted py-4">No rounds generated yet</div>');
            return;
        }

        // State: which round is selected
        let selectedRound = window.OBZG_SELECTED_ROUND || rounds[rounds.length - 1].round;
        let showAll = selectedRound === 'all';

        // Render round selector bar
        let navHtml = '<nav class="obzg-round-tabs"><ul class="pagination justify-content-center mb-4">';
        navHtml += `<li class="page-item${showAll || selectedRound === rounds[0].round ? ' disabled' : ''}"><a class="page-link obzg-round-nav" href="#" data-round="prev">&lt;</a></li>`;
        rounds.forEach((round, idx) => {
            navHtml += `<li class="page-item${!showAll && selectedRound === round.round ? ' active' : ''}"><a class="page-link obzg-round-nav" href="#" data-round="${round.round}">${round.round}</a></li>`;
        });
        navHtml += `<li class="page-item${showAll ? ' active' : ''}"><a class="page-link obzg-round-nav" href="#" data-round="all">All</a></li>`;
        navHtml += `<li class="page-item${showAll || selectedRound === rounds[rounds.length-1].round ? ' disabled' : ''}"><a class="page-link obzg-round-nav" href="#" data-round="next">&gt;</a></li>`;
        navHtml += '</ul></nav>';

        // Helper: get Swiss points from all previous rounds for a club
        function getSwissPoints(clubId, upToRound) {
            let pts = 0;
            for (let r = 0; r < upToRound - 1; r++) {
                const round = rounds[r];
                if (!round) continue;
                for (const match of round.matches) {
                    if (match.result) {
                        if (match.club1_id == clubId) {
                            if (match.club2_id === 0) {
                                pts += 3;
                            } else if (match.result.club1_score > match.result.club2_score) {
                                pts += 3;
                            } else if (match.result.club1_score === match.result.club2_score) {
                                pts += 1;
                            }
                        } else if (match.club2_id == clubId) {
                            if (match.club1_id === 0) {
                                pts += 3;
                            } else if (match.result.club2_score > match.result.club1_score) {
                                pts += 3;
                            } else if (match.result.club2_score === match.result.club1_score) {
                                pts += 1;
                            }
                        }
                    }
                }
            }
            return (upToRound > 1) ? `(${pts} pts)` : '';
        }

        let html = navHtml;
        if (showAll) {
            rounds.forEach((round, roundIndex) => {
                html += `<div class="round-section mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-calendar-week"></i> Round ${round.round}
                    </h6>
                    <div class="row">
                `;
                round.matches.forEach((match, matchIndex) => {
                    const matchId = `round-${round.round}-match-${matchIndex}`;
                    const hasResult = match.result && match.result.club1_score !== null;
                    const resultClass = hasResult ? 'border-success' : 'border-warning';
                    const club1Swiss = match.club1_id ? getSwissPoints(match.club1_id, round.round) : '';
                    const club2Swiss = match.club2_id ? getSwissPoints(match.club2_id, round.round) : '';
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card ${resultClass} h-100 match-card">
                                <div class="card-header bg-light">
                                    <small class="text-muted">Match ${matchIndex + 1}</small>
                                </div>
                                <div class="card-body p-3">
                                    <div class="text-center mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-start flex-grow-1">
                                                <div class="club-name">${match.club1_name} <span class='text-secondary small'>${club1Swiss}</span></div>
                                                ${hasResult ? `<div class="club-score">${match.result.club1_score}</div>` : ''}
                                            </div>
                                            <div class="mx-3">
                                                <span class="badge match-vs-badge">VS</span>
                                            </div>
                                            <div class="text-end flex-grow-1">
                                                <div class="club-name">${match.club2_name === 'BYE' ? 'BYE' : match.club2_name} ${match.club2_name !== 'BYE' ? `<span class='text-secondary small'>${club2Swiss}</span>` : ''}</div>
                                                ${hasResult && match.club2_name !== 'BYE' ? `<div class="club-score">${match.result.club2_score}</div>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    ${!hasResult && match.club2_name !== 'BYE' ? `
                                        <div class="text-center">
                                            <button class="btn btn-sm btn-outline-primary enter-result-btn" 
                                                    data-match-id="${matchId}" 
                                                    data-club1="${match.club1_name}" 
                                                    data-club2="${match.club2_name}"
                                                    data-round="${round.round}"
                                                    data-match-index="${matchIndex}">
                                                <i class="bi bi-pencil"></i> Enter Result
                                            </button>
                                        </div>
                                    ` : hasResult ? `
                                        <div class="text-center">
                                            <span class="badge result-recorded-badge">Result Recorded</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div></div>';
            });
        } else {
            const roundObj = rounds.find(r => r.round === selectedRound) || rounds[rounds.length - 1];
            html += `<div class="round-section mb-4">
                <h6 class="text-primary mb-3">
                    <i class="bi bi-calendar-week"></i> Round ${roundObj.round}
                </h6>
                <div class="row">
            `;
            roundObj.matches.forEach((match, matchIndex) => {
                const matchId = `round-${roundObj.round}-match-${matchIndex}`;
                const hasResult = match.result && match.result.club1_score !== null;
                const resultClass = hasResult ? 'border-success' : 'border-warning';
                const club1Swiss = match.club1_id ? getSwissPoints(match.club1_id, roundObj.round) : '';
                const club2Swiss = match.club2_id ? getSwissPoints(match.club2_id, roundObj.round) : '';
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card ${resultClass} h-100 match-card">
                            <div class="card-header bg-light">
                                <small class="text-muted">Match ${matchIndex + 1}</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="text-center mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-start flex-grow-1">
                                            <div class="club-name">${match.club1_name} <span class='text-secondary small'>${club1Swiss}</span></div>
                                            ${hasResult ? `<div class="club-score">${match.result.club1_score}</div>` : ''}
                                        </div>
                                        <div class="mx-3">
                                            <span class="badge match-vs-badge">VS</span>
                                        </div>
                                        <div class="text-end flex-grow-1">
                                            <div class="club-name">${match.club2_name === 'BYE' ? 'BYE' : match.club2_name} ${match.club2_name !== 'BYE' ? `<span class='text-secondary small'>${club2Swiss}</span>` : ''}</div>
                                            ${hasResult && match.club2_name !== 'BYE' ? `<div class="club-score">${match.result.club2_score}</div>` : ''}
                                        </div>
                                    </div>
                                </div>
                                ${!hasResult && match.club2_name !== 'BYE' ? `
                                    <div class="text-center">
                                        <button class="btn btn-sm btn-outline-primary enter-result-btn" 
                                                data-match-id="${matchId}" 
                                                data-club1="${match.club1_name}" 
                                                data-club2="${match.club2_name}"
                                                data-round="${roundObj.round}"
                                                data-match-index="${matchIndex}">
                                            <i class="bi bi-pencil"></i> Enter Result
                                        </button>
                                    </div>
                                ` : hasResult ? `
                                    <div class="text-center">
                                        <span class="badge result-recorded-badge">Result Recorded</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div></div>';
        }
        container.html(html);

        // Tab click handlers
        container.find('.obzg-round-nav').off('click').on('click', function(e) {
            e.preventDefault();
            let newRound = selectedRound;
            const val = $(this).data('round');
            if (val === 'prev') {
                if (showAll) {
                    newRound = rounds[rounds.length - 1].round;
                } else {
                    const idx = rounds.findIndex(r => r.round === selectedRound);
                    if (idx > 0) newRound = rounds[idx - 1].round;
                }
            } else if (val === 'next') {
                if (showAll) {
                    newRound = rounds[rounds.length - 1].round;
                } else {
                    const idx = rounds.findIndex(r => r.round === selectedRound);
                    if (idx < rounds.length - 1) newRound = rounds[idx + 1].round;
                }
            } else if (val === 'all') {
                newRound = 'all';
            } else {
                newRound = parseInt(val, 10);
            }
            window.OBZG_SELECTED_ROUND = newRound;
            renderRounds(rounds);
        });
    }

    function generateNextRound() {
        const roundNumber = $('#next-round-number').val() || 1;
        
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_generate_swiss_rounds',
                tournament_id: currentTournament.id,
                round_number: roundNumber,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.data.message, 'success');
                    loadTournamentStandings(currentTournament.id);
                    // Update the round number input for the next round
                    $('#next-round-number').val(roundNumber + 1);
                } else {
                    showAlert('Error generating round: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to generate round', 'danger');
            }
        });
    }

    function saveMatchResult(matchId) {
        const club1Score = $('#club1-score').val();
        const club2Score = $('#club2-score').val();
        
        if (club1Score === '' || club2Score === '') {
            showAlert('Please enter both scores', 'warning');
            return;
        }
        
        // Parse match information from the matchId
        const matchInfo = matchId.split('-');
        const roundNumber = parseInt(matchInfo[1]);
        const matchIndex = parseInt(matchInfo[3]);
        
        // Find the actual match data
        const rounds = currentTournament.rounds || [];
        const round = rounds.find(r => r.round === roundNumber);
        
        if (!round || !round.matches[matchIndex]) {
            showAlert('Match not found', 'danger');
            return;
        }
        
        const match = round.matches[matchIndex];
        
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_save_match_result',
                tournament_id: currentTournament.id,
                round_number: roundNumber,
                match_index: matchIndex,
                club1_id: match.club1_id,
                club2_id: match.club2_id,
                club1_score: club1Score,
                club2_score: club2Score,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Match result saved successfully!', 'success');
                    // Close modal using Bootstrap 5 API
                    const modalElement = document.getElementById('match-result-modal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    loadTournamentStandings(currentTournament.id);
                } else {
                    showAlert('Error saving result: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to save result', 'danger');
            }
        });
    }

    // Team Management Functions
    function showTeamManagement(tournamentId) {
        const tournament = tournaments.find(t => t.id == tournamentId);
        if (!tournament) {
            showAlert('Tournament not found', 'danger');
            return;
        }

        currentTournament = tournament;
        renderTeamManagementInterface();
        loadAvailableTeams(tournamentId);
    }

    function renderTeamManagementInterface() {
        const container = $('#obzg-tournament-admin-root');
        const currentTeams = currentTournament.clubs ? currentTournament.clubs.length : 0;
        const maxTeams = currentTournament.max_teams || 0;
        const teamLimitText = maxTeams > 0 ? `${currentTeams}/${maxTeams} teams` : `${currentTeams} teams (unlimited)`;
        
        container.html(`
            <div class="tournament-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="bi bi-people"></i> Team Management</h1>
                        <p>${currentTournament.title} - ${teamLimitText}</p>
                    </div>
                    <button class="btn btn-outline-light obzg-back-to-tournaments-btn">
                        <i class="bi bi-arrow-left"></i> Back to Tournaments
                    </button>
                </div>
            </div>
            
            <div id="alerts-container"></div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shuffle"></i> Quick Add Teams</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="random-teams-count" class="form-label">Number of Teams</label>
                                    <input type="number" class="form-control" id="random-teams-count" min="1" max="50" value="16">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary add-random-teams-btn">
                                        <i class="bi bi-shuffle"></i> Add Random Teams
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">This will randomly select teams from available clubs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Current Teams</h5>
                        </div>
                        <div class="card-body">
                            <div id="current-teams-container">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                    <p>Loading current teams...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Available Teams</h5>
                        </div>
                        <div class="card-body">
                            <div id="available-teams-container">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-search" style="font-size: 2rem;"></i>
                                    <p>Loading available teams...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    function loadAvailableTeams(tournamentId) {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_get_available_teams',
                tournament_id: tournamentId,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderAvailableTeams(response.data.clubs);
                    renderCurrentTeams();
                } else {
                    showAlert('Error loading available teams: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to load available teams', 'danger');
            }
        });
    }

    function renderCurrentTeams() {
        const container = $('#current-teams-container');
        const currentTeams = currentTournament.clubs || [];
        
        if (currentTeams.length === 0) {
            container.html('<div class="text-center text-muted py-4">No teams added yet</div>');
            return;
        }

        let html = '<div class="list-group">';
        currentTeams.forEach(function(teamId) {
            const team = clubs.find(c => c.id == teamId);
            if (team) {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${team.title}</h6>
                            <small class="text-muted">${team.city || 'No city'}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-team-btn" data-team-id="${team.id}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                `;
            }
        });
        html += '</div>';
        container.html(html);
    }

    function renderAvailableTeams(availableTeams) {
        const container = $('#available-teams-container');
        const currentTeams = currentTournament.clubs || [];
        const maxTeams = currentTournament.max_teams || 0;
        const canAddMore = maxTeams === 0 || currentTeams.length < maxTeams;
        
        if (availableTeams.length === 0) {
            container.html('<div class="text-center text-muted py-4">No available teams to add</div>');
            return;
        }

        if (!canAddMore) {
            container.html('<div class="text-center text-muted py-4">Tournament is full</div>');
            return;
        }

        let html = '<div class="list-group">';
        availableTeams.forEach(function(team) {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${team.title}</h6>
                        <small class="text-muted">${team.city || 'No city'} • ${team.president || 'No president'}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-success add-team-btn" data-team-id="${team.id}">
                        <i class="bi bi-plus"></i> Add
                    </button>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }

    function addTeamToTournament(teamId) {
        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_add_team_to_tournament',
                tournament_id: currentTournament.id,
                team_id: teamId,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Team added successfully!', 'success');
                    // Refresh tournament data
                    loadTournaments();
                    loadAvailableTeams(currentTournament.id);
                    renderCurrentTeams();
                } else {
                    showAlert('Error adding team: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to add team', 'danger');
            }
        });
    }

    function removeTeamFromTournament(teamId) {
        if (!confirm('Are you sure you want to remove this team from the tournament?')) {
            return;
        }

        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_remove_team_from_tournament',
                tournament_id: currentTournament.id,
                team_id: teamId,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Team removed successfully!', 'success');
                    // Refresh tournament data
                    loadTournaments();
                    loadAvailableTeams(currentTournament.id);
                    renderCurrentTeams();
                } else {
                    showAlert('Error removing team: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to remove team', 'danger');
            }
        });
    }

    function addRandomTeams(numTeams) {
        if (!confirm(`Are you sure you want to add ${numTeams} random teams to the tournament?`)) {
            return;
        }

        $.ajax({
            url: OBZG_AJAX_TOURNAMENTS.ajax_url,
            type: 'POST',
            data: {
                action: 'obzg_add_random_teams',
                tournament_id: currentTournament.id,
                num_teams: numTeams,
                _ajax_nonce: OBZG_AJAX_TOURNAMENTS.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.data.message, 'success');
                    // Refresh tournament data
                    loadTournaments();
                    loadAvailableTeams(currentTournament.id);
                    renderCurrentTeams();
                } else {
                    showAlert('Error adding random teams: ' + response.data.message, 'danger');
                }
            },
            error: function() {
                showAlert('Failed to add random teams', 'danger');
            }
        });
    }

    // Initialize the application
    init();
}); 