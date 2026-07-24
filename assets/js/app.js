async function deletePoll(pollId, redirectUrl) {
    if (!confirm("Delete this poll? This can't be undone.")) return;

    try {
        const formData = new FormData();
        formData.append("poll_id", pollId);

        const response = await fetch("api/delete_poll.php", {
            method: "POST",
            body: formData
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Delete failed.");
        }

        window.location.href = redirectUrl || "dashboard.php";
    } catch (err) {
        alert("Error: " + err.message);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const votingForm = document.getElementById("voting-form");
    if (votingForm) {
        votingForm.addEventListener("submit", handleVoteSubmission);
    }
    
    if (document.getElementById("results-container")) {
        const urlParams = new URLSearchParams(window.location.search);
        const pollId = urlParams.get('id');
        fetchResultsMetrics(pollId);
        setInterval(() => fetchResultsMetrics(pollId), 4000);
    }
});

async function handleVoteSubmission(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch("api/vote.php", {
            method: "POST",
            body: formData
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Submission failed.");
        }

        alert(result.message);
        window.location.reload();
    } catch (err) {
        alert("Error: " + err.message);
    }
}

async function fetchResultsMetrics(pollId) {
    try {
        const response = await fetch(`api/get_results.php?poll_id=${pollId}`);
        const data = await response.json();

        if (data.success) {
            data.metrics.forEach(opt => {
                const percentage = data.total_votes > 0 ? ((opt.vote_count / data.total_votes) * 100).toFixed(1) : 0;
                const progressFill = document.getElementById(`progress-fill-${opt.id}`);
                const textualLabel = document.getElementById(`count-label-${opt.id}`);
                
                if (progressFill) progressFill.style.width = `${percentage}%`;
                if (textualLabel) textualLabel.textContent = `${opt.vote_count} votes (${percentage}%)`;
            });
        }
    } catch (error) {
        console.error("Metrics sync error:", error);
    }
}