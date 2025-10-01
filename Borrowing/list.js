fetch("list.php")
  .then(res => res.json())
  .then(data => {
    const tbody = document.getElementById("reserved-books");
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No reserved books found.</td></tr>`;
    } else {
      data.forEach(book => {
        tbody.innerHTML += `
          <tr>
            <td class="px-6 py-4">${book.title}</td>
            <td class="px-6 py-4">${book.author}</td>
            <td class="px-6 py-4 text-green-600 font-semibold">${book.status}</td>
            <td class="px-6 py-4">${book.date_borrowed}</td>
          </tr>
        `;
      });
    }
  });