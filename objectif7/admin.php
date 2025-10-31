<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
requireAdmin();

// --- DATA FETCHING (Same as before) ---
$query = "
    SELECT 
        t.id_team, t.nom AS team_nom, t.description,
        u.id_user, u.username, u.prenom, u.nom AS user_nom, u.grade, u.email, u.role
    FROM teams t
    LEFT JOIN team_members tm ON t.id_team = tm.team_id
    LEFT JOIN users u ON tm.usr_id = u.id_user
    ORDER BY t.nom ASC, u.nom ASC
";
$result = $mysqli->query($query);
$data_rows = $result->fetch_all(MYSQLI_ASSOC);
$teams_with_members = [];
foreach ($data_rows as $row) {
    $teams_with_members[$row['id_team']]['id_team'] = $row['id_team'];
    $teams_with_members[$row['id_team']]['team_nom'] = $row['team_nom'];
    $teams_with_members[$row['id_team']]['description'] = $row['description'];
    if ($row['id_user']) {
        $teams_with_members[$row['id_team']]['members'][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Gestion des Équipes</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Gestion des Équipes et Membres</h1>
        <a href="actions/logout.php" class="logout-btn">Déconnexion</a>
    </div>

    <div class="admin-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <!-- The Main Management Table -->
        <table class="management-table">
            <thead>
                <tr>
                    <th>Équipe</th>
                    <th>Membre</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams_with_members as $team): ?>
                    <?php $member_count = isset($team['members']) ? count($team['members']) : 1; ?>
                    <tr>
                        <td rowspan="<?php echo $member_count; ?>">
                            <strong><?php echo htmlspecialchars($team['team_nom']); ?></strong>
                            <div style="margin-top: 10px;">
                                <a href="#" class="action-link edit-team-link"
                                   data-id="<?php echo $team['id_team']; ?>"
                                   data-nom="<?php echo htmlspecialchars($team['team_nom']); ?>"
                                   data-desc="<?php echo htmlspecialchars($team['description']); ?>">
                                   Modifier
                                </a>
                                <a href="actions/delete_team.php?id=<?php echo $team['id_team']; ?>" class="action-link delete-link"
                                   onclick="return confirm('Attention ! Supprimer cette équipe supprimera aussi tous ses membres. Continuer ?');">
                                   Supprimer
                                </a>
                            </div>
                        </td>
                        <?php if (isset($team['members'])): ?>
                            <td><?php echo htmlspecialchars($team['members'][0]['prenom'] . ' ' . $team['members'][0]['user_nom']); ?></td>
                            <td><?php echo htmlspecialchars($team['members'][0]['grade']); ?></td>
                            <td>
                                <a href="#" class="action-link edit-member-link"
                                   data-id="<?php echo $team['members'][0]['id_user']; ?>"
                                   data-prenom="<?php echo htmlspecialchars($team['members'][0]['prenom']); ?>"
                                   data-nom="<?php echo htmlspecialchars($team['members'][0]['user_nom']); ?>"
                                   data-grade="<?php echo htmlspecialchars($team['members'][0]['grade']); ?>">
                                   Modifier
                                </a>
                                <a href="actions/delete_member.php?id=<?php echo $team['members'][0]['id_user']; ?>" class="action-link delete-link"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                   Supprimer
                                </a>
                            </td>
                        <?php else: ?>
                            <td colspan="3" style="text-align:center;">Aucun membre dans cette équipe.</td>
                        <?php endif; ?>
                    </tr>
                    <?php if (isset($team['members']) && $member_count > 1): ?>
                        <?php for ($i = 1; $i < $member_count; $i++): $member = $team['members'][$i]; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['prenom'] . ' ' . $member['user_nom']); ?></td>
                            <td><?php echo htmlspecialchars($member['grade']); ?></td>
                            <td>
                                <a href="#" class="action-link edit-member-link"
                                   data-id="<?php echo $member['id_user']; ?>"
                                   data-prenom="<?php echo htmlspecialchars($member['prenom']); ?>"
                                   data-nom="<?php echo htmlspecialchars($member['user_nom']); ?>"
                                   data-grade="<?php echo htmlspecialchars($member['grade']); ?>">
                                   Modifier
                                </a>
                                <a href="actions/delete_member.php?id=<?php echo $member['id_user']; ?>" class="action-link delete-link"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                   Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button id="showAddTeamForm" class="btn-primary" style="margin-top: 20px;">Ajouter une équipe</button>
        <button id="showAddMemberForm" class="btn-secondary" style="margin-top: 20px;">Ajouter un membre à une équipe</button>

        <!-- HIDDEN FORMS (These will appear below the button, in the page flow) -->
        <div id="addFormContainer" class="form-container" style="display: none;">
            <form action="actions/add_team_and_members.php" method="POST">
                <h2>Ajouter une nouvelle équipe</h2>
                <div class="form-group"><label>Nom de l'équipe: <input type="text" name="team_nom" required></label></div>
                <div class="form-group"><label>Description: <textarea name="description"></textarea></label></div>
                <hr>
                <h4>Ajouter des membres initiaux</h4>
                <div id="members-container"></div>
                <button type="button" id="add-member-field" class="btn btn-add">Ajouter un membre</button>
                <hr>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" class="btn btn-secondary close-form">Annuler</button>
            </form>
        </div>
        <!-- FORM TO ADD A MEMBER TO AN EXISTING TEAM -->
        <div id="addMemberFormContainer" class="form-container" style="display: none;">
            <form action="actions/add_member_to_team.php" method="POST">
                <h2>Ajouter un membre à une équipe existante</h2>
                <div class="form-group">
                    <label for="team_id">Choisir l'équipe :</label>
                    <select name="team_id" id="team_id" required>
                        <option value="">-- Sélectionner une équipe --</option>
                        <?php foreach ($teams_with_members as $team): ?>
                            <option value="<?php echo $team['id_team']; ?>">
                                <?php echo htmlspecialchars($team['team_nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr>
                <h4>Détails du nouveau membre</h4>
                <div class="member-field-group">
                    <input type="text" name="prenom" placeholder="Prénom" required autocomplete="off">
                    <input type="text" name="nom" placeholder="Nom" required autocomplete="off">
                    <input type="text" name="username" placeholder="Nom d'utilisateur" required autocomplete="off">
                    <input type="password" name="password" placeholder="Mot de passe" required autocomplete="new-password">
                    <input type="email" name="email" placeholder="Email" required autocomplete="off">
                    <input type="text" name="grade" placeholder="Grade" required autocomplete="off">
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Enregistrer le membre</button>
                <button type="button" class="btn btn-secondary close-form">Annuler</button>
            </form>
        </div>

        <div id="editTeamFormContainer" class="form-container" style="display: none;">
            <form action="actions/edit_team.php" method="POST">
                <h2>Modifier l'équipe</h2>
                <input type="hidden" name="id_team" id="edit_team_id">
                <div class="form-group"><label>Nom de l'équipe: <input type="text" name="nom" id="edit_team_nom" required></label></div>
                <div class="form-group"><label>Description: <textarea name="description" id="edit_team_desc"></textarea></label></div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <button type="button" class="btn btn-secondary close-form">Annuler</button>
            </form>
        </div>

        <div id="editMemberFormContainer" class="form-container" style="display: none;">
            <form action="actions/edit_member.php" method="POST">
                <h2>Modifier le Membre</h2>
                <input type="hidden" name="id_user" id="edit_member_id">
                <div class="form-group"><label>Prénom: <input type="text" name="prenom" id="edit_member_prenom" required></label></div>
                <div class="form-group"><label>Nom: <input type="text" name="nom" id="edit_member_nom" required></label></div>
                <div class="form-group"><label>Grade/Statut: <input type="text" name="grade" id="edit_member_grade" required></label></div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <button type="button" class="btn btn-secondary close-form">Annuler</button>
            </form>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.js"></script>
    <script src="assets/js/jquery-3.6.0.js"></script>
    <script>
        $(document).ready(function() {
            // Get references to ALL form containers
            const $addTeamForm = $('#addFormContainer');
            const $addMemberForm = $('#addMemberFormContainer');
            const $editTeamForm = $('#editTeamFormContainer');
            const $editMemberForm = $('#editMemberFormContainer');
            const $allForms = $('.form-container');

            // --- "AJOUTER UNE ÉQUIPE" FORM ---
            $('#showAddTeamForm').on('click', function() {
                $allForms.not($addTeamForm).slideUp();
                $('#members-container').empty();
                addMemberField();
                $addTeamForm.slideToggle();
            });

            // --- "AJOUTER UN MEMBRE" FORM (NEW) ---
            $('#showAddMemberForm').on('click', function() {
                $allForms.not($addMemberForm).slideUp();
                $addMemberForm.slideToggle();
            });

            // Function to add member fields to the "Add Team" form
            function addMemberField() {
                const fieldHtml = `
                    <div class="member-field-group">
                        <input type="text" name="prenom[]" placeholder="Prénom" required autocomplete="off">
                        <input type="text" name="nom[]" placeholder="Nom" required autocomplete="off">
                        <input type="text" name="username[]" placeholder="Nom d'utilisateur" required autocomplete="off">
                        <input type="password" name="password[]" placeholder="Mot de passe" required autocomplete="new-password">
                        <input type="email" name="email[]" placeholder="Email" required autocomplete="off">
                        <input type="text" name="grade[]" placeholder="Grade" required autocomplete="off">
                        <button type="button" class="remove-member-field">X</button>
                    </div>`;
                $('#members-container').append(fieldHtml);
            }
            $('#add-member-field').on('click', addMemberField);
            $('#members-container').on('click', '.remove-member-field', function() {
                $(this).closest('.member-field-group').remove();
            });

            // --- EDIT TEAM FORM ---
            $('.edit-team-link').on('click', function(e) {
                e.preventDefault();
                $allForms.not($editTeamForm).slideUp();
                $('#edit_team_id').val($(this).data('id'));
                $('#edit_team_nom').val($(this).data('nom'));
                $('#edit_team_desc').val($(this).data('desc'));
                $editTeamForm.slideDown();
            });

            // --- EDIT MEMBER FORM ---
            $('.edit-member-link').on('click', function(e) {
                e.preventDefault();
                $allForms.not($editMemberForm).slideUp();
                $('#edit_member_id').val($(this).data('id'));
                $('#edit_member_prenom').val($(this).data('prenom'));
                $('#edit_member_nom').val($(this).data('nom'));
                $('#edit_member_grade').val($(this).data('grade'));
                $editMemberForm.slideDown();
            });

            // --- CANCEL/CLOSE FORM BUTTON ---
            $('.close-form').on('click', function() {
                $(this).closest('.form-container').slideUp();
            });
        });
    </script>
</body>
</html>