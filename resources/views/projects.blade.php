<!doctype html>
<html>
    <head>
    <meta charset="utf-8">
    <title>Drilling Projects</title>
    <style>
        body{font-family:system-ui;margin:20px}
        table{border-collapse:collapse;width:100%}
        th,td{border:1px solid #ccc;padding:6px}
        .small{font-size:0.9em;color:#666}
    </style>
    </head>
    <body>
    <h1>Projects & Procedures</h1>
    <p id="status">Loading...</p>
    <table id="tbl" style="display:none">
    <thead>
        <tr>
        <th>Auftragsnummer</th>
        <th>Project Name</th>
        <th>Procedure Start</th>
        <th>End</th>
        <th>Processes</th>
        </tr>
    </thead>
    <tbody id="tbody"></tbody>
    </table>

    <script>
    async function load() {
    const status = document.getElementById('status');
    try {
        const res = await fetch('/api/projects');
        const data = await res.json();
        console.log(data);
        document.getElementById('tbl').style.display='';
        status.style.display='none';
        const tbody = document.getElementById('tbody');
        tbody.innerHTML='';
        data.data.forEach(p=>{
        p.procedures.forEach(proc=>{
            const tr=document.createElement('tr');
            const procs=proc.processes.map(pr=>`${pr.name} (count:${pr.count}, time:${Math.floor(pr.total_seconds/60)}m ${pr.total_seconds%60}s)`).join('<br>');
            tr.innerHTML=`
            <td>${p.auftragsnummer}</td>
            <td>${p.project_name}</td>
            <td>${proc.start_time??''}</td>
            <td>${proc.end_time??''}</td>
            <td>${procs}</td>`;
            tbody.appendChild(tr);
        });
        });
    } catch(err) {
        status.textContent='Error: '+err.message;
    }
    }
    load();
    </script>
    </body>
</html>
