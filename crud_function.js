const deleteEndpoint = "/capstone/delete_student.php";

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
    // Send the request
    fetch(request)
        .then(response => {
            // Check if the request was successful
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            // If successful, return the response text
            alert("Data deleted successfully");
            row.remove();
        })
        .then(data => {
            // Log the response data
            console.log(data);
            // You can do something with the response data here
        })
        .catch(error => {
            // Log any errors
            console.error('There was a problem with your fetch operation:', error);
        });
  }
}