document.addEventListener('DOMContentLoaded', async () => {
  const target = document.getElementById('audit-log-table');
  if (!target) return;

  await loadAuditLogs();

  // Filter event listeners
  document.getElementById('filterRole')?.addEventListener('input', applyFilters);
  document.getElementById('filterAction')?.addEventListener('input', applyFilters);
  document.getElementById('filterUser')?.addEventListener('input', applyFilters);
  document.getElementById('clearFilters')?.addEventListener('click', clearAllFilters);
});

let allLogs = [];
let filteredLogs = [];
let currentPage = 1;
const LOGS_PER_PAGE = 25;

async function loadAuditLogs() {
  const target = document.getElementById('audit-log-table');
  target.innerHTML = '<p class="loading"><i class="fas fa-spinner fa-spin"></i> Loading audit logs...</p>';

  try {
    const res = await fetch('/backend/sql_handler/auditlog_table.php', {
      method: 'GET',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }

    allLogs = await res.json();

    if (allLogs.error) {
      target.innerHTML = `<p class="error">${escapeHtml(allLogs.message)}</p>`;
      return;
    }

    if (!Array.isArray(allLogs) || allLogs.length === 0) {
      target.innerHTML = '<p class="no-results">No audit logs found.</p>';
      return;
    }

    filteredLogs = [...allLogs];
    currentPage = 1;
    renderLogs();
  } catch (e) {
    console.error('Audit log error:', e);
    target.innerHTML = `<p class="error"><i class="fas fa-exclamation-circle"></i> Failed to load: ${escapeHtml(e.message)}</p>`;
  }
}

function applyFilters() {
  const roleFilter = document.getElementById('filterRole')?.value.toLowerCase() || '';
  const actionFilter = document.getElementById('filterAction')?.value.toLowerCase() || '';
  const userFilter = document.getElementById('filterUser')?.value.toLowerCase() || '';

  filteredLogs = allLogs.filter(log => {
    const matchRole = !roleFilter || String(log.role).toLowerCase().includes(roleFilter);
    const matchAction = !actionFilter || String(log.action).toLowerCase().includes(actionFilter);
    const matchUser = !userFilter || String(log.userID).toLowerCase().includes(userFilter);
    return matchRole && matchAction && matchUser;
  });

  currentPage = 1;
  renderLogs();
}

function clearAllFilters() {
  document.getElementById('filterRole').value = '';
  document.getElementById('filterAction').value = '';
  document.getElementById('filterUser').value = '';
  filteredLogs = [...allLogs];
  currentPage = 1;
  renderLogs();
}

function renderLogs() {
  const target = document.getElementById('audit-log-table');

  if (!filteredLogs.length) {
    target.innerHTML = '<p class="no-results">No logs match your filters.</p>';
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  const totalPages = Math.ceil(filteredLogs.length / LOGS_PER_PAGE);
  const offset = (currentPage - 1) * LOGS_PER_PAGE;
  const pageData = filteredLogs.slice(offset, offset + LOGS_PER_PAGE);

  const tableHtml = `
    <table class="table-base">
      <thead>
        <tr>
          <th>Log ID</th>
          <th>Date & Time</th>
          <th>User ID</th>
          <th>Role</th>
          <th>Action</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        ${pageData.map(log => `
          <tr>
            <td>#${escapeHtml(String(log.logID))}</td>
            <td class="timestamp">${formatDateTime(log.createdAt)}</td>
            <td>${escapeHtml(String(log.userID))}</td>
            <td><span class="role-badge role-${escapeHtml(String(log.role).toLowerCase())}">${escapeHtml(String(log.role))}</span></td>
            <td><span class="action-badge action-${getActionClass(log.action)}">${escapeHtml(String(log.action))}</span></td>
            <td class="details-text" title="${escapeHtml(String(log.details || ''))}">${escapeHtml(String(log.details || '-'))}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;

  target.innerHTML = tableHtml;

  // Render pagination
  const paginationHtml = `
    <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">
      <i class="fas fa-chevron-left"></i> Previous
    </button>
    <span class="pagination-info">Page ${currentPage} of ${totalPages}</span>
    <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">
      Next <i class="fas fa-chevron-right"></i>
    </button>
  `;

  document.getElementById('pagination').innerHTML = paginationHtml;
}

function goToPage(page) {
  const totalPages = Math.ceil(filteredLogs.length / LOGS_PER_PAGE);
  if (page >= 1 && page <= totalPages) {
    currentPage = page;
    renderLogs();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

function getActionClass(action) {
  const lower = String(action).toLowerCase();
  if (lower.includes('login')) return 'login';
  if (lower.includes('dispense')) return 'dispense';
  if (lower.includes('add') || lower.includes('create')) return 'add';
  if (lower.includes('edit') || lower.includes('update')) return 'edit';
  if (lower.includes('delete')) return 'delete';
  return 'default';
}

function formatDateTime(dt) {
  if (!dt) return '-';
  const d = new Date(dt);
  if (isNaN(d.getTime())) return dt;
  return d.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}