$(document).ready(function () {
  // Initialize: Normalize the table on page load
  // Convert rowspan structure to flat structure where each row has team info
  function initializeTable() {
    const $tbody = $("#teamsTable tbody");
    const $rows = $tbody.find("tr");
    const normalizedRows = [];

    let currentTeam = "";

    $rows.each(function () {
      const $row = $(this);
      const $teamCell = $row.find("th[scope='row']");

      // If this row has a team cell, update current team
      if ($teamCell.length > 0) {
        currentTeam = $teamCell.text().trim();
      }

      // Get member and status (accounting for team cell presence)
      const $cells = $row.find("td");
      const member = $cells.eq(0).text().trim();
      const status = $cells.eq(1).text().trim();

      // Store normalized data
      normalizedRows.push({
        team: currentTeam,
        member: member,
        status: status,
      });
    });

    // Rebuild table in flat format with data attributes
    $tbody.empty();
    normalizedRows.forEach((rowData) => {
      const $newRow = $("<tr>")
        .attr("data-team", rowData.team)
        .attr("data-member", rowData.member)
        .attr("data-status", rowData.status);

      $newRow.append($("<td>").addClass("team-col").text(rowData.team));
      $newRow.append($("<td>").text(rowData.member));
      $newRow.append($("<td>").text(rowData.status));

      $tbody.append($newRow);
    });

    // Apply rowspan display
    applyRowspans();
  }

  // Apply rowspans to visually group consecutive identical teams
  function applyRowspans() {
    const $tbody = $("#teamsTable tbody");
    const $rows = $tbody.find("tr:visible"); // Only work with visible rows

    if ($rows.length === 0) return;

    // First, make all team cells visible and remove rowspans
    $tbody.find("tr").each(function () {
      const $row = $(this);
      const team = $row.attr("data-team");

      // Remove existing team cell structure
      $row.find(".team-col").remove();

      // Add team cell back
      const $teamCell = $("<td>").addClass("team-col").text(team);
      $row.prepend($teamCell);
    });

    // Now apply rowspans only to visible rows
    let i = 0;
    while (i < $rows.length) {
      const $currentRow = $rows.eq(i);
      const currentTeam = $currentRow.attr("data-team");
      let spanCount = 1;

      // Count consecutive rows with same team
      let j = i + 1;
      while (
        j < $rows.length &&
        $rows.eq(j).attr("data-team") === currentTeam
      ) {
        spanCount++;
        j++;
      }

      // Apply rowspan to first row
      const $teamCell = $currentRow.find(".team-col");
      if (spanCount > 1) {
        $teamCell.attr("rowspan", spanCount);

        // Hide team cells in subsequent rows of this group
        for (let k = i + 1; k < i + spanCount; k++) {
          $rows.eq(k).find(".team-col").hide();
        }
      } else {
        $teamCell.removeAttr("rowspan");
      }

      i += spanCount;
    }
  }

  // Initialize table on load
  initializeTable();

  // Add new row
  $("#addRowForm").on("submit", function (event) {
    event.preventDefault();

    const team = $("#team").val().trim();
    const member = $("#member").val().trim();
    const status = $("#status").val().trim();

    // Create new row with data attributes
    const $newRow = $("<tr>")
      .attr("data-team", team)
      .attr("data-member", member)
      .attr("data-status", status);

    $newRow.append($("<td>").addClass("team-col").text(team));
    $newRow.append($("<td>").text(member));
    $newRow.append($("<td>").text(status));

    $("#teamsTable tbody").append($newRow);

    // Reapply rowspans
    applyRowspans();

    // Reapply current search if active
    const teamSearch = $("#searchTeam").val();
    const memberSearch = $("#searchMember").val();
    const statusSearch = $("#searchStatus").val();
    if (teamSearch || memberSearch || statusSearch) {
      searchTable();
    }

    this.reset();
  });

  // Sort functionality
  $("#sortButton").on("click", function () {
    const sortColumn = $("#sortColumn").val();
    const sortOrder = $("#sortOrder").val();
    const $tbody = $("#teamsTable tbody");

    // Get all rows as array
    const $rows = $tbody.find("tr").get();

    // Sort the array
    $rows.sort((a, b) => {
      let valueA, valueB;

      if (sortColumn === "team") {
        valueA = $(a).attr("data-team");
        valueB = $(b).attr("data-team");
      } else if (sortColumn === "member") {
        valueA = $(a).attr("data-member");
        valueB = $(b).attr("data-member");
      } else {
        // status
        valueA = $(a).attr("data-status");
        valueB = $(b).attr("data-status");
      }

      const comparison = valueA.localeCompare(valueB);
      return sortOrder === "asc" ? comparison : -comparison;
    });

    // Reorder rows in DOM
    $.each($rows, function (index, row) {
      $tbody.append(row);
    });

    // Reapply rowspans after sorting
    applyRowspans();
  });

  // Search functionality
  $("#searchButton").on("click", searchTable);

  $("#searchTeam, #searchMember, #searchStatus").on("keypress", function (e) {
    if (e.which === 13) {
      searchTable();
    }
  });

  function searchTable() {
    const teamSearch = $("#searchTeam").val().toLowerCase().trim();
    const memberSearch = $("#searchMember").val().toLowerCase().trim();
    const statusSearch = $("#searchStatus").val().toLowerCase().trim();

    const $tbody = $("#teamsTable tbody");
    const $rows = $tbody.find("tr");

    // If no search terms, show all rows
    if (!teamSearch && !memberSearch && !statusSearch) {
      $rows.show();
      applyRowspans();
      return;
    }

    // Filter rows based on search criteria
    $rows.each(function () {
      const $row = $(this);
      const team = $row.attr("data-team").toLowerCase();
      const member = $row.attr("data-member").toLowerCase();
      const status = $row.attr("data-status").toLowerCase();

      const teamMatch = !teamSearch || team.includes(teamSearch);
      const memberMatch = !memberSearch || member.includes(memberSearch);
      const statusMatch = !statusSearch || status.includes(statusSearch);

      if (teamMatch && memberMatch && statusMatch) {
        $row.show();
      } else {
        $row.hide();
      }
    });

    // Reapply rowspans to visible rows only
    applyRowspans();
  }

  // Reset functionality
  $("#resetButton").on("click", function () {
    // Clear search inputs
    $("#searchTeam, #searchMember, #searchStatus").val("");

    // Show all rows
    $("#teamsTable tbody tr").show();

    // Reapply rowspans
    applyRowspans();
  });
});
