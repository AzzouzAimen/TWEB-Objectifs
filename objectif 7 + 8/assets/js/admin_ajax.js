$(document).ready(function () {
  console.log("Admin AJAX script loaded");

  /**
   * Shows loading state on submit button
   */
  function setButtonLoading(button, loadingText) {
    button.prop("disabled", true).text(loadingText);
  }

  /**
   * Resets button to normal state
   */
  function resetButton(button, originalText) {
    button.prop("disabled", false).text(originalText);
  }

  /**
   * Closes form and resets it
   */
  function closeAndResetForm(formContainer, form, additionalCleanup) {
    formContainer.slideUp();
    form.trigger("reset");
    if (additionalCleanup) additionalCleanup();
  }

  /**
   *  error handler for AJAX calls
   */
  function handleAjaxError(xhr, status, error, button, buttonText) {
    console.error("AJAX Error:", status, error);
    console.log("Response Status:", xhr.status);
    console.log("Response Text:", xhr.responseText);
    alert(
      "Une erreur de communication est survenue. Vérifiez la console pour plus de détails."
    );
    resetButton(button, buttonText);
  }

  /**
   * Generates HTML for member action links
   */
  function generateMemberActionsHtml(member) {
    return `
      <a href="#" class="action-link edit-member-link"
         data-id="${member.id_user}"
         data-prenom="${member.prenom}"
         data-nom="${member.nom}"
         data-grade="${member.grade}">
         Modifier
      </a>
      <a href="actions/delete_member.php?id=${member.id_user}" class="action-link delete-link">
         Supprimer
      </a>
    `;
  }

  /**
   * Generic AJAX form submission handler
   */
  function handleFormSubmit(config) {
    return function (e) {
      e.preventDefault();
      const form = $(this);
      const submitButton = form.find('button[type="submit"]');

      setButtonLoading(submitButton, config.loadingText);

      $.ajax({
        type: "POST",
        url: form.attr("action"),
        data: form.serialize(),
        dataType: "json",
        beforeSend: config.beforeSend,
        success: function (response) {
          if (config.onSuccess) {
            console.log("AJAX Success Response:", response);
          }

          if (response.success) {
            if (config.onSuccess) {
              config.onSuccess(response, form, submitButton);
            }
          } else {
            alert("Erreur: " + response.message);
            resetButton(submitButton, config.buttonText);
          }
        },
        error: function (xhr, status, error) {
          handleAjaxError(xhr, status, error, submitButton, config.buttonText);
        },
      });
    };
  }

  // ============================================================================
  // FORM HANDLERS
  // ============================================================================

  // --- AJAX for ADDING a new team and members ---
  $("#addFormContainer form").on(
    "submit",
    handleFormSubmit({
      loadingText: "Enregistrement...",
      buttonText: "Enregistrer",
      onSuccess: function (response, form, submitButton) {
        closeAndResetForm($("#addFormContainer"), form, () =>
          $("#members-container").empty()
        );
        window.location.href = window.location.pathname;
      },
    })
  );

  // --- AJAX for ADDING a member to an existing team ---
  $("#addMemberFormContainer form").on(
    "submit",
    handleFormSubmit({
      loadingText: "Enregistrement...",
      buttonText: "Enregistrer le membre",
      beforeSend: function () {
        console.log("Sending AJAX request with X-Requested-With header");
      },
      onSuccess: function (response, form, submitButton) {
        closeAndResetForm($("#addMemberFormContainer"), form);

        const member = response.member;
        const teamRow = $(
          `.edit-team-link[data-id="${member.team_id}"]`
        ).closest("tr");
        const noMemberRow = teamRow.find('td[colspan="3"]');

        if (noMemberRow.length > 0) {
          // Replace "Aucun membre" placeholder with the new member
          noMemberRow.parent().html(`
          <td>${member.prenom} ${member.nom}</td>
          <td>${member.grade}</td>
          <td>${generateMemberActionsHtml(member)}</td>
        `);
          teamRow.find("td:first").attr("rowspan", 1);
        } else {
          // Add new row after existing members
          const newRow = `
          <tr>
            <td>${member.prenom} ${member.nom}</td>
            <td>${member.grade}</td>
            <td>${generateMemberActionsHtml(member)}</td>
          </tr>
        `;
          teamRow.after(newRow);

          const currentRowspan = parseInt(
            teamRow.find("td:first").attr("rowspan") || 1
          );
          teamRow.find("td:first").attr("rowspan", currentRowspan + 1);
        }

        resetButton(submitButton, "Enregistrer le membre");
      },
    })
  );

  // --- AJAX for EDITING a team ---
  $("#editTeamFormContainer form").on(
    "submit",
    handleFormSubmit({
      loadingText: "Mise à jour...",
      buttonText: "Mettre à jour",
      onSuccess: function (response, form, submitButton) {
        $("#editTeamFormContainer").slideUp();

        const team = response.team;
        const teamLink = $(`.edit-team-link[data-id="${team.id_team}"]`);

        teamLink.closest("td").find("strong").text(team.nom);
        teamLink.attr("data-nom", team.nom).attr("data-desc", team.description);

        resetButton(submitButton, "Mettre à jour");
      },
    })
  );

  // --- AJAX for EDITING a member ---
  $("#editMemberFormContainer form").on(
    "submit",
    handleFormSubmit({
      loadingText: "Mise à jour...",
      buttonText: "Mettre à jour",
      onSuccess: function (response, form, submitButton) {
        $("#editMemberFormContainer").slideUp();

        const member = response.member;
        const memberLink = $(`.edit-member-link[data-id="${member.id_user}"]`);
        const memberRow = memberLink.closest("tr");

        memberRow.find("td:eq(0)").text(`${member.prenom} ${member.nom}`);
        memberRow.find("td:eq(1)").text(member.grade);

        memberLink
          .attr("data-prenom", member.prenom)
          .attr("data-nom", member.nom)
          .attr("data-grade", member.grade);

        resetButton(submitButton, "Mettre à jour");
      },
    })
  );

  // ============================================================================
  // DELETE HANDLERS (using event delegation for dynamically added elements)
  // ============================================================================

  /**
   * AJAX handler for deleting a member
   */
  $(document).on(
    "click",
    "a.delete-link[href*='delete_member.php']",
    function (e) {
      e.preventDefault();

      if (!confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
        return;
      }

      const deleteLink = $(this);
      const url = deleteLink.attr("href");
      const urlParams = new URLSearchParams(url.split("?")[1]);
      const memberId = urlParams.get("id");

      $.ajax({
        type: "POST",
        url: "actions/delete_member.php",
        data: { id: memberId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            console.log("Member deleted successfully");

            // Find the row containing this member
            const memberRow = deleteLink.closest("tr");
            const teamCell = memberRow.find("td[rowspan]");

            // Check if this member's row has the team cell (first member)
            if (teamCell.length > 0) {
              // This is the first member row with the team name
              const currentRowspan = parseInt(teamCell.attr("rowspan") || 1);

              if (currentRowspan > 1) {
                // Team has other members - move team cell to next row
                const nextRow = memberRow.next("tr");
                const teamCellClone = teamCell.clone();
                teamCellClone.attr("rowspan", currentRowspan - 1);
                nextRow.prepend(teamCellClone);
                memberRow.remove();
              } else {
                // Last member - show "Aucun membre" placeholder
                memberRow.html(`
                <td colspan="3" style="text-align:center;">Aucun membre dans cette équipe.</td>
              `);
              }
            } else {
              // Not the first row - just remove it and update rowspan
              const firstRow = memberRow
                .prevAll("tr")
                .filter(function () {
                  return $(this).find("td[rowspan]").length > 0;
                })
                .first();

              const teamCell = firstRow.find("td[rowspan]");
              const currentRowspan = parseInt(teamCell.attr("rowspan") || 1);
              teamCell.attr("rowspan", currentRowspan - 1);
              memberRow.remove();
            }
          } else {
            alert("Erreur: " + response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          console.log("Response:", xhr.responseText);
          alert("Une erreur est survenue lors de la suppression.");
        },
      });
    }
  );

  /**
   * AJAX handler for deleting a team
   */
  $(document).on(
    "click",
    "a.delete-link[href*='delete_team.php']",
    function (e) {
      e.preventDefault();

      if (
        !confirm(
          "Attention ! Supprimer cette équipe supprimera aussi tous ses membres. Continuer ?"
        )
      ) {
        return;
      }

      const deleteLink = $(this);
      const url = deleteLink.attr("href");
      const urlParams = new URLSearchParams(url.split("?")[1]);
      const teamId = urlParams.get("id");

      $.ajax({
        type: "POST",
        url: "actions/delete_team.php",
        data: { id: teamId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            console.log("Team deleted successfully");

            // Find the team row (contains the team cell with rowspan)
            const teamCell = $(`.edit-team-link[data-id="${teamId}"]`).closest(
              "td"
            );
            const firstRow = teamCell.closest("tr");
            const rowspan = parseInt(teamCell.attr("rowspan") || 1);

            // Collect all rows to delete (first row + member rows)
            const rowsToDelete = [firstRow];
            let currentRow = firstRow;
            for (let i = 1; i < rowspan; i++) {
              currentRow = currentRow.next("tr");
              if (currentRow.length > 0) {
                rowsToDelete.push(currentRow);
              }
            }

            // Remove all rows at once
            rowsToDelete.forEach((row) => row.remove());
          } else {
            alert("Erreur: " + response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          console.log("Response:", xhr.responseText);
          alert("Une erreur est survenue lors de la suppression.");
        },
      });
    }
  );
});
