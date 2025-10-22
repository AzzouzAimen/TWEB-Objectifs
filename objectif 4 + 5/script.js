const form = document.getElementById("addRowForm");
const table = document.getElementById("teamsTable");

form.addEventListener("submit", function (event) {
  // listen for the  submit event
  event.preventDefault(); // stop from reloading on submit

  // read values and remove space
  const teamName = document.getElementById("team").value.trim();
  const memberName = document.getElementById("member").value.trim();
  const status = document.getElementById("status").value.trim();

  if (!teamName || !memberName || !status) return;

  // search the table for a th  for that team
  let existing_team_th = null;

  //filtre to only th with rowspan
  const teamHeaders = Array.from(table.getElementsByTagName("th")).filter(th => th.hasAttribute("rowspan"));

  // compare in lowercase
  teamHeaders.forEach((th) => {
    if (th.innerText.trim().toLowerCase() === teamName.toLowerCase()) {
      existing_team_th = th;
    }
  });

  // function to create a new row
  function add_row({teamName,memberName,status,includeTh = false}) {
    const newRow = document.createElement("tr");

    if (includeTh) {
      const th = document.createElement("th");
      th.setAttribute("rowspan", "1");
      th.innerText = teamName;
      newRow.appendChild(th);
    }

    const td1 = document.createElement("td");
    td1.innerText = memberName;
    const td2 = document.createElement("td");
    td2.innerText = status;

    newRow.appendChild(td1);
    newRow.appendChild(td2);

    return newRow;
  }

  if (existing_team_th) {     // if team exists, find the last row of that team and insert after it
    let rowspan = parseInt(existing_team_th.getAttribute("rowspan"));
    const firstrow = existing_team_th.parentElement; // first row that has <th> is the parent <tr>
    let currentrow = firstrow;

    for (let i = 1; i < rowspan; i++) {
      currentrow = currentrow.nextElementSibling;
    }

    // create a new row but no <th> since it exist
    const newRow = add_row({ memberName, status, includeTh: false });
    currentrow.after(newRow);
    existing_team_th.setAttribute("rowspan", rowspan + 1);

  } else {    // if the team doesn't exist, create a new row with a <th>

    const newRow = add_row({teamName,memberName,status,includeTh: true});
    table.appendChild(newRow);
  }

  form.reset(); // empty form for next submit
});
