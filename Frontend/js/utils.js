// Frontend/js/utils.js
const API_BASE = "http://localhost:8080/job-portal-system/helper/api";

export const api = {
  get: (url) =>
    fetch(`${API_BASE}/${url}`, { credentials: "include" }).then((r) =>
      r.json(),
    ),
  post: (url, data) =>
    fetch(`${API_BASE}/${url}`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    }).then((r) => r.json()),
  put: (url, data) =>
    fetch(`${API_BASE}/${url}`, {
      method: "PUT",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    }).then((r) => r.json()),
  delete: (url) =>
    fetch(`${API_BASE}/${url}`, {
      method: "DELETE",
      credentials: "include",
    }).then((r) => r.json()),
};

export const Auth = {
  // Store user in localStorage with key "jobportal_user"
  getUser: () => {
    try {
      return JSON.parse(localStorage.getItem("jobportal_user") || "null");
    } catch {
      return null;
    }
  },
  setUser: (user) =>
    localStorage.setItem("jobportal_user", JSON.stringify(user)),
  logout: () => {
    localStorage.removeItem("jobportal_user");
    sessionStorage.clear(); // clear any leftover session data
  },
  // Redirect to the correct dashboard based on role
  redirect: (role) => {
    window.location.href =
      role === "employer" ? "employer-dashboard.html" : "seeker-dashboard.html";
  },
};

export function escapeHtml(str) {
  return String(str || "").replace(/[&<>]/g, function (m) {
    if (m === "&") return "&amp;";
    if (m === "<") return "&lt;";
    if (m === ">") return "&gt;";
    return m;
  });
}

export function formatSalary(min, max) {
  if (!min || min <= 0) return "Competitive";
  const fmt = (n) => "$" + Number(n).toLocaleString();
  return max && max > 0 ? `${fmt(min)} – ${fmt(max)}` : `${fmt(min)}+`;
}

export function formatDate(dateStr) {
  if (!dateStr) return "—";
  const d = new Date(dateStr);
  const diff = Math.floor((new Date() - d) / 86400000);
  if (diff === 0) return "Today";
  if (diff === 1) return "Yesterday";
  if (diff < 7) return `${diff}d ago`;
  return d.toLocaleDateString();
}

export function statusBadge(status) {
  const map = {
    open: "badge-success",
    closed: "badge-secondary",
    pending: "badge-warning",
    accepted: "badge-success",
    rejected: "badge-danger",
  };
  const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : "—";
  return `<span class="badge ${map[status] || "badge-secondary"}">${label}</span>`;
}

export function showToast(msg, type = "success") {
  const toast = document.createElement("div");
  toast.className = `toast-global toast-${type}`;
  toast.innerHTML = `<i class="fa-solid ${type === "success" ? "fa-circle-check" : "fa-circle-exclamation"}"></i> ${msg}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
