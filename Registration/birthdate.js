document.addEventListener("DOMContentLoaded", () => {
    const birthdateInput = document.getElementById('birthdate');
    const errorMessage = document.getElementById('birthdate-error');
    const today = new Date();
    const minAge = 12;

    // Calculate max valid birthdate (today minus 12 years)
    const maxDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
    const formattedMaxDate = maxDate.toISOString().split('T')[0];
    birthdateInput.max = formattedMaxDate;

    birthdateInput.addEventListener('input', () => {
    const selectedDate = new Date(birthdateInput.value);
    const age = today.getFullYear() - selectedDate.getFullYear();
    const monthDiff = today.getMonth() - selectedDate.getMonth();
    const dayDiff = today.getDate() - selectedDate.getDate();

    const isTooYoung = (
        age < minAge ||
        (age === minAge && (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)))
    );
    });

});