document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('sa-form');
    const classSelect = document.getElementById('sa-class');
    const issueSelect = document.getElementById('sa-issue');

    if (!form || !classSelect || !issueSelect) return;

    const issueOptions = Array.from(issueSelect.options);

    function filterIssues() {
        const selectedClassId = classSelect.value;

        issueSelect.innerHTML = '';

        issueOptions.forEach(option => {
            if (!option.value || option.dataset.classId === selectedClassId) {
                issueSelect.appendChild(option);
            }
        });

        issueSelect.value = '';
    }

    classSelect.addEventListener('change', filterIssues);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const issueVal = issueSelect.value;

        if (!classSelect.value || !issueVal) {
            alert('Please select both Class and Issue');
            return;
        }

        window.location.href = `/situation/${issueVal}/`;
    });

    filterIssues();
});