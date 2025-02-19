const deleteEndpoint = "/delete_student.php";
const editEndpoint = "/edit_student_info.php";
const addEndpoint = "/add_student_info.php";

/**
 * Deletes a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function deleteStudent(button) {
  /** @type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /** @type {string} */
  const dataId = row.getAttribute("data-id");
  /** @type {boolean} */
  const result = confirm("Do you really want to delete?");

  const url = `${deleteEndpoint}?school_id=${encodeURIComponent(dataId)}`;
  const request = new Request(url, {
    method: "DELETE",
  });

  if (result) {
    fetch(request)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }

        return response.text();
      })
      .then((text) => {
        row.remove(); // Remove the row from the table
        alert(text);
      })
      .catch(error => {
        console.error('There was a problem with your fetch operation:', error);
      });
  }
}

/**
 * Edits the details of a row from the table based on the button clicked.
 * @param {HTMLButtonElement} button - The button element clicked.
 */
function editStudentInfo(button) {
  // Get the closest tr element (row) from the clicked button
  const row = button.closest('tr');
  if (!row) {
    console.error('Could not find parent row');
    return;
  }

  // Get the data-id attribute from the row
  const dataId = row.getAttribute("data-id");
  if (!dataId) {
    console.error('No data-id found on row');
    return;
  }

  // Get the edit modal
  const editModal = document.getElementById('editModal');
  if (!editModal) {
    console.error('Edit modal not found');
    return;
  }

  // Populate form fields with correct cell indices
  // Note: cells[0] is checkbox, so data starts at index 1
  document.getElementById('editSchoolId').value = row.cells[1].textContent.trim();  // School ID
  document.getElementById('editFirstName').value = row.cells[2].textContent.trim(); // First Name
  document.getElementById('editLastName').value = row.cells[3].textContent.trim();  // Last Name
  document.getElementById('editMI').value = row.cells[4].textContent.trim();        // MI
  document.getElementById('editSuffix').value = row.cells[5].textContent.trim();    // Suffix
  document.getElementById('editGender').value = row.cells[6].textContent.trim();    // Gender
  document.getElementById('editSemester').value = row.cells[7].textContent.trim();  // Semester
  document.getElementById('editNSTP').value = row.cells[8].textContent.trim();      // NSTP
  document.getElementById('editDepartment').value = row.cells[9].textContent.trim(); // College
  document.getElementById('editCourse').value = row.cells[10].textContent.trim();    // Program

  // Show the modal
  editModal.showModal();

  // Set up form submission handler
  document.getElementById('editForm').onsubmit = function (event) {
    event.preventDefault();
    saveEdit(dataId, row);
  };
}

/**
 * Closes the edit modal dialog.
 */
function closeModal() {
  const editModal = document.getElementById('editModal');
  editModal.close();
}

/**
 * Saves the edited details of a student.
 * @param {string} dataId - The ID of the student.
 * @param {HTMLTableRowElement} row - The table row of the student to be edited.
 */
function saveEdit(dataId, row) {
  const editForm = document.getElementById('editForm');
  const formData = new FormData(editForm);

  const url = `${editEndpoint}?school_id=${encodeURIComponent(dataId)}`;

  fetch(url, {
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(() => {
      alert('Data updated successfully');
      updateTableRow(row); // Update the row in the table
      closeModal(); // Close the modal
    })
    .catch(error => {
      console.error('There was a problem with your fetch operation:', error);
    });
}

/**
 * Updates the table row after editing with the new data.
 * @param {HTMLTableRowElement} row - The table row to be updated.
 */
function updateTableRow(row) {
  // Update each cell with the new values
  row.cells[1].textContent = document.getElementById('editSchoolId').value;
  row.cells[2].textContent = document.getElementById('editFirstName').value;
  row.cells[3].textContent = document.getElementById('editLastName').value;
  row.cells[4].textContent = document.getElementById('editMI').value;
  row.cells[5].textContent = document.getElementById('editSuffix').value;
  row.cells[6].textContent = document.getElementById('editGender').value;
  row.cells[7].textContent = document.getElementById('editSemester').value;
  row.cells[8].textContent = document.getElementById('editNSTP').value;
  row.cells[9].textContent = document.getElementById('editDepartment').value;
  row.cells[10].textContent = document.getElementById('editCourse').value;
}

/**
 * Adds a new student to the table.
 * @param {object} newData - Data of the new student.
 */
function addTableRow(newData) {
  // Create new table row
  const newRow = document.createElement('tr');
  newRow.setAttribute('data-id', newData.school_id);

  // Create checkbox cell
  const checkboxCell = document.createElement('td');
  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.className = 'selectStudentCheckbox';
  checkbox.onclick = function() { toggleSelectionActions(); };
  checkboxCell.appendChild(checkbox);
  newRow.appendChild(checkboxCell);

  // Create table data cells for each column
  const columns = ['school_id', 'first_name', 'last_name', 'mi', 'suffix', 'gender', 'semester', 'nstp', 'department', 'course'];
  columns.forEach(column => {
    const cell = document.createElement('td');
    cell.textContent = newData[column] || ''; // Add empty string fallback for null values
    newRow.appendChild(cell);
  });

  // Create action buttons cell
  const actionCell = document.createElement('td');
  
  // Edit button
  const editButton = document.createElement('button');
  editButton.className = 'editButton';
  editButton.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
  editButton.addEventListener('click', function () {
    editStudentInfo(this);
  });
  
  // Delete button
  const deleteButton = document.createElement('button');
  deleteButton.className = 'deleteButton';
  deleteButton.innerHTML = '<i class="fa-solid fa-trash"></i>';
  deleteButton.addEventListener('click', function () {
    deleteStudent(this);
  });
  
  // Assign button
  const assignButton = document.createElement('button');
  assignButton.className = 'assignButton';
  assignButton.innerHTML = '<i class="fa-solid fa-user-plus"></i>';
  assignButton.onclick = checkAndOpenConfirmPopup;
  
  actionCell.appendChild(editButton);
  actionCell.appendChild(deleteButton);
  actionCell.appendChild(assignButton);
  newRow.appendChild(actionCell);

  // Append the new row to the table body
  document.getElementById('tableBody').appendChild(newRow);
}

/**
 * Submits add student form data.
 */
function submitAddForm() {
  const addForm = document.getElementById('addForm');
  const formData = new URLSearchParams(new FormData(addForm)); // Serialize form data

  fetch(addEndpoint, {
    method: "POST",
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded' // Set content type
    },
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(data => {
      if (data.includes("Success")) {
        closeAddModal();
        alert('Student added successfully');
        window.location.reload(); // Reload the page or refresh table data if needed
      } else {
        alert(data); // Display the response from the server
      }
    })
    .catch(error => {
      console.error('There was a problem with your fetch operation:', error);
      alert('Error: Student ID is already added to the system');
    });
}

// Attach the submit event listener
document.getElementById('addForm').addEventListener('submit', function (event) {
  event.preventDefault(); // Prevent form submission
  submitAddForm();
});

// Function to open add student modal
function openAddModal() {
  const addModal = document.getElementById('addModal');
  addModal.showModal();
}

// Function to close add student modal
function closeAddModal() {
  const addModal = document.getElementById('addModal');
  addModal.close();
}

