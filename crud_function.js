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
  /**@type {HTMLTableRowElement} */
  const row = button.parentElement.parentElement;
  /** @type {string} */
  const dataId = row.getAttribute("data-id");

  document.getElementById('editSchoolId').value = dataId;
  document.getElementById('editFirstName').value = row.children[2].textContent;
  document.getElementById('editLastName').value = row.children[3].textContent;
  document.getElementById('editGender').value = row.children[4].textContent;
  document.getElementById('editSemester').value = row.children[5].textContent;
  document.getElementById('editNSTP').value = row.children[6].textContent;
  document.getElementById('editDepartment').value = row.children[7].textContent;
  document.getElementById('editCourse').value = row.children[8].textContent;

  const editModal = document.getElementById('editModal');
  editModal.showModal();

  document.getElementById('editForm').onsubmit = function (event) {
    event.preventDefault();
    saveEdit(dataId, row); // Pass the row to saveEdit function
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

  // Debug log
  console.log('Sending update for student ID:', dataId);
  for (let pair of formData.entries()) {
    console.log(pair[0] + ': ' + pair[1]);
  }

  const url = `${editEndpoint}?school_id=${encodeURIComponent(dataId)}`;

  fetch(url, {
    method: "POST",
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(text => {
          throw new Error(text || 'Network response was not ok');
        });
      }
      return response.text();
    })
    .then(data => {
      if (data.includes("Success")) {
        alert('Student updated successfully');
        // Update the row data
        row.children[1].textContent = formData.get('school_id');
        row.children[2].textContent = formData.get('first_name');
        row.children[3].textContent = formData.get('last_name');
        row.children[4].textContent = formData.get('gender');
        row.children[5].textContent = formData.get('semester');
        row.children[6].textContent = formData.get('nstp');
        row.children[7].textContent = formData.get('department');
        row.children[8].textContent = formData.get('course');
        
        closeModal();
        window.location.reload(); // Force page reload
      } else {
        throw new Error(data);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to update student: ' + error.message);
    });
}

/**
 * Updates the table row after editing with the new data.
 * @param {HTMLTableRowElement} row - The table row to be updated.
 */
function updateTableRow(row) {
  row.children[1].textContent = document.getElementById('editSchoolId').value;
  row.children[2].textContent = document.getElementById('editFirstName').value;
  row.children[3].textContent = document.getElementById('editLastName').value;
  row.children[4].textContent = document.getElementById('editGender').value;
  row.children[5].textContent = document.getElementById('editSemester').value;
  row.children[6].textContent = document.getElementById('editNSTP').value;
  row.children[7].textContent = document.getElementById('editDepartment').value;
  row.children[8].textContent = document.getElementById('editCourse').value;
}

/**
 * Adds a new student to the table.
 * @param {object} newData - Data of the new student.
 */
function addTableRow(newData) {
  // Create new table row
  const newRow = document.createElement('tr');
  newRow.setAttribute('data-id', newData.school_id);

  // Create table data cells for each column
  const columns = ['school_id', 'first_name', 'last_name', 'gender', 'semester', 'nstp', 'department', 'course'];
  columns.forEach(column => {
    const cell = document.createElement('td');
    cell.textContent = newData[column];
    newRow.appendChild(cell);
  });

  // Create action buttons cell
  const actionCell = document.createElement('td');
  const editButton = document.createElement('button');
  editButton.textContent = 'Edit';
  editButton.className = 'editButton';
  editButton.addEventListener('click', function () {
    editStudentInfo(this);
  });
  const deleteButton = document.createElement('button');
  deleteButton.textContent = 'Delete';
  deleteButton.className = 'deleteButton';
  deleteButton.addEventListener('click', function () {
    deleteStudent(this);
  });
  actionCell.appendChild(editButton);
  actionCell.appendChild(deleteButton);
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
      alert('An error occurred while adding the student');
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

