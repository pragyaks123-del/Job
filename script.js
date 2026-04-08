// 1. Fetch and Display Jobs with Search Filters
async function fetchJobs() {
    const keyword  = document.getElementById('keyword').value;
    const location = document.getElementById('location').value;
    const category = document.getElementById('category').value;
    const type     = document.getElementById('job_type').value;
    const salary   = document.getElementById('min_salary').value;

    const container = document.getElementById('job-list');
    container.innerHTML = '<p style="color:#888; padding:20px;">Loading jobs...</p>';

    try {
        const params = new URLSearchParams({
            keyword,
            location,
            category,
            type,
            salary
        });

        const res = await fetch(`search.php?${params.toString()}`);
        if (!res.ok) {
            throw new Error('Server error');
        }

        const jobs = await res.json();
        container.innerHTML = '';

        if (!Array.isArray(jobs) || jobs.length === 0) {
            container.innerHTML = '<p style="color:#888; padding:20px;">No matching jobs found.</p>';
            return;
        }

        // Store jobs in a global map so onclick can look them up by id safely
        window._jobMap = {};
        jobs.forEach(job => {
            window._jobMap[job.id] = job;

            container.innerHTML += `
                <div class="job-card">
                    <div>
                        <h3>${job.title}</h3>
                        <small>${job.location} | ${job.job_type} | ${job.category}</small><br>
                        <strong style="font-size:0.88rem;">Max Salary: $${job.salary_max}</strong>
                    </div>
                    <div class="btn-group">
                        <button class="fav-btn" onclick="saveJob(${job.id})">❤️ Save</button>
                        <button class="apply-btn" onclick="goToApplyPage(${job.id}, '${encodeURIComponent(job.title)}')">
                            Apply Now
                        </button>
                    </div>
                </div>`;
        });
    } catch (error) {
        console.error('Failed to fetch jobs:', error);
        container.innerHTML = '<p style="color:#c0392b; padding:20px;">Failed to load jobs. Please try again.</p>';
    }
}

// 2. Redirect to Application Page
function goToApplyPage(id, title) {
    window.location.href = `apply.html?id=${id}&title=${title}`;
}

// 3. Save Job to Favorites
async function saveJob(jobId) {
    const job = window._jobMap[jobId];

    if (!job) {
        alert('Job data not found. Please refresh and try again.');
        return;
    }

    try {
        const payload = {
            user_id:    1,
            job_id:     job.id,
            title:      job.title,
            category:   job.category,
            location:   job.location,
            job_type:   job.job_type,
            salary_max: job.salary_max
        };

        const response = await fetch('save_favorite.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        });

        const result = await response.json();
        alert(result.message);
    } catch (error) {
        console.error('Save failed:', error);
        alert('Failed to save job. Please try again.');
    }
}

// Initial load
window.onload = () => {
    if (document.getElementById('job-list')) fetchJobs();
};
