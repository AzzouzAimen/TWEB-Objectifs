$(document).ready(function () {
  let isInitialLoad = true;

  // Rebuild table from AJAX data
  function rebuildTableFromData(teams) {
    const $tbody = $("#teamsTable tbody");
    $tbody.empty();

    teams.forEach(function (team) {
      team.members.forEach(function (member) {
        let statusDisplay = member.grade;

        const $row = $("<tr>")
          .attr("data-team", team.nom)
          .attr("data-member", member.prenom + " " + member.nom)
          .attr("data-status", statusDisplay);

        $row.append($("<td>").addClass("team-col").text(team.nom));
        $row.append($("<td>").text(member.prenom + " " + member.nom));
        $row.append($("<td>").text(statusDisplay));

        $tbody.append($row);
      });
    });

    // Call applyRowspans from script.js
    window.applyRowspans();
  }

  // Fetch updated data from server
  function fetchTeamsData() {
    $.ajax({
      type: "GET",
      url: "actions/get_teams_data.php",
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Only update if this is not the initial load (to avoid flickering)
          if (!isInitialLoad) {
            
            rebuildTableFromData(response.teams);

            // Reapply current search filters if any
            const teamSearch = $("#searchTeam").val();
            const memberSearch = $("#searchMember").val();
            const statusSearch = $("#searchStatus").val();

            if (teamSearch || memberSearch || statusSearch) {
              // Call searchTable from script.js
              window.searchTable();
            }

          }
          isInitialLoad = false;
        }
      },
      error: function (xhr, status, error) {
        console.error("Error fetching teams data:", error);
      },
    });
  }

  //  Polling for updates every 5 seconds
  setInterval(fetchTeamsData, 5000);

  //  fetch immediately to establish initial timestamp
  setTimeout(fetchTeamsData, 1000);
});
