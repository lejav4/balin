(function($){
    function getModalHtml(match) {
        var isEdit = !!match;
        return `
            <div class="modal fade obzg-modal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5);">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">${isEdit ? 'Edit Match' : 'Add Match'}</h5>
                    <button type="button" class="btn-close obzg-modal-cancel" aria-label="Close"></button>
                  </div>
                  <form id="obzg-match-form">
                    <div class="modal-body">
                      <input type="hidden" name="match_id" value="${isEdit ? match.id : ''}">
                      <div class="mb-3">
                        <label for="obzg-match-title" class="form-label">Match Name</label>
                        <input type="text" class="form-control" id="obzg-match-title" name="match_title" value="${isEdit ? match.title : ''}" required />
                      </div>
                      <div class="mb-3">
                        <label for="obzg-match-desc" class="form-label">Description</label>
                        <textarea class="form-control" id="obzg-match-desc" name="match_desc">${isEdit ? (match.desc || '') : ''}</textarea>
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
    function renderMatchesTable(matches) {
        if (!matches.length) {
            return '<div class="alert alert-info">No matches found.</div>';
        }
        var html = '<table class="table table-striped"><thead><tr><th>Name</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
        matches.forEach(function(match){
            html += `<tr>
                <td>${match.title}</td>
                <td>${match.desc || ''}</td>
                <td>
                    <button class="btn btn-sm btn-primary obzg-edit-match-btn" data-id="${match.id}">Edit</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        return html;
    }
    function openMatchModal(match) {
        closeMatchModal();
        var $modal = $(getModalHtml(match));
        $('body').append($modal);
        setTimeout(function(){ $modal.addClass('show'); }, 10);
        $('.obzg-modal-cancel').on('click', closeMatchModal);
        $('#obzg-match-form').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = {
                action: 'obzg_save_match',
                _ajax_nonce: OBZG_AJAX_MATCHES.nonce,
                match_id: $form.find('[name="match_id"]').val(),
                match_title: $form.find('[name="match_title"]').val(),
                match_desc: $form.find('[name="match_desc"]').val()
            };
            $form.find('button[type="submit"]').prop('disabled', true);
            $.post(OBZG_AJAX_MATCHES.ajax_url, data, function(resp){
                $form.find('button[type="submit"]').prop('disabled', false);
                if(resp.success) {
                    closeMatchModal();
                    loadMatches();
                } else {
                    var msg = resp.data && resp.data.message ? resp.data.message : 'Error saving match.';
                    $form.prepend('<div class="alert alert-danger">'+msg+'</div>');
                }
            }).fail(function(){
                $form.find('button[type="submit"]').prop('disabled', false);
                $form.prepend('<div class="alert alert-danger">AJAX error.</div>');
            });
        });
    }
    function closeMatchModal() {
        $('.obzg-modal').remove();
    }
    function loadMatches() {
        $('#obzg-match-admin-root').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $.ajax({
            url: OBZG_AJAX_MATCHES.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_match',
                _ajax_nonce: OBZG_AJAX_MATCHES.nonce
            },
            success: function(resp) {
                if (resp.success && Array.isArray(resp.data)) {
                    $('#obzg-match-admin-root').html(renderMatchesTable(resp.data));
                } else {
                    $('#obzg-match-admin-root').html('<div class="alert alert-danger">Failed to load matches.</div>');
                }
            },
            error: function() {
                $('#obzg-match-admin-root').html('<div class="alert alert-danger">AJAX error loading matches.</div>');
            }
        });
    }
    $(document).on('click', '#obzg-add-match-btn', function(){
        openMatchModal();
    });
    $(document).on('click', '.obzg-edit-match-btn', function(){
        var matchId = $(this).data('id');
        $.ajax({
            url: OBZG_AJAX_MATCHES.ajax_url,
            method: 'POST',
            data: {
                action: 'obzg_get_single_match',
                _ajax_nonce: OBZG_AJAX_MATCHES.nonce,
                match_id: matchId
            },
            success: function(resp) {
                if (resp.success && resp.data) {
                    openMatchModal(resp.data);
                } else {
                    alert('Failed to load match data.');
                }
            },
            error: function() {
                alert('AJAX error loading match data.');
            }
        });
    });
    $(function(){
        if($('#obzg-match-admin-root').length) {
            loadMatches();
            $('#obzg-match-admin-root').before('<button id="obzg-add-match-btn" class="btn btn-success mb-3">Add Match</button>');
        }
    });
})(jQuery); 