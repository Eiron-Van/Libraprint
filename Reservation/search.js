document.querySelector("button").addEventListener("click", () => {
  const selected = document.querySelector("input[name='selectedBook']:checked");
  if (selected) {
    alert("You reserved: " + selected.value);
  } else {
    alert("Please select a book first!");
  }
});
