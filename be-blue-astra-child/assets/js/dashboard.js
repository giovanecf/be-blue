import { Grid, html } from "https://unpkg.com/gridjs?module";

/**
 * CHARTS RENDER
 */

/**
 * TABLE RENDER
 */

const ptBR = {
  search: { placeholder: "Digite uma palavra-chave..." },
  sort: {
    sortAsc: "Coluna em ordem crescente",
    sortDesc: "Coluna em ordem decrescente",
  },
  pagination: {
    previous: "Anterior",
    next: "Próxima",
    navigate: function (e, r) {
      return "Página " + e + " de " + r;
    },
    page: function (e) {
      return "Página " + e;
    },
    showing: "Mostrando",
    of: "de",
    to: "até",
    results: "resultados",
  },
  loading: "Carregando...",
  noRecordsFound: "Nenhum registro encontrado",
  error: "Ocorreu um erro ao buscar os dados",
};

/**
 * REVENUES
 */

function revenuesActionColumn(_, row) {
  const html_content = `
      <div class="d-flex gap-2">
          <a class="btn btn-outline-info" href="/atualizar-status-despesa/?id=${row.cells[0].data}&status=Paga" title="Registrar Entrada">
              <i class="bi bi-box-arrow-in-down"></i>
          </a>
          <a class="btn btn-outline-secondary" href="/vizualizar-objeto/?id=${row.cells[0].data}" title="Vizualizar objeto">
              <i class="bi bi-ticket-detailed"></i>
          </a>
          <a class="btn btn-outline-primary" href="/editar-receita/?id=${row.cells[0].data}" title="Editar despesa">
              <i class="bi bi-pencil"></i>
          </a>
          <a class="btn btn-outline-danger" href="/excluir-objeto/?id=${row.cells[0].data}" title="Excluir despesa">
              <i class="bi bi-trash"></i>
          </a>
      </div>  
        `;

  return html(html_content);
}

new gridjs.Grid({
  columns: [
    "ID",
    "Desc.",
    {
      name: "Valor",
      formatter: (_, row) => "$ " + row.cells[2].data,
    },
    "Freq.",
    {
      name: "Data",
      formatter: (_, row) => getDateStr(row.cells[4].data),
    },
    {
      name: "Ações",
      formatter: revenuesActionColumn,
    },
  ],
  data: fetchRevenuesDataHadler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("wrapper_revenues"));

function fetchRevenuesDataHadler() {
  console.log(revenues);
  const submissions = revenues;

  let table_arr = [];
  for (const submission of submissions) {
    const data = JSON.parse(submission.data);

    table_arr.push([
      submission["id"],
      data["desc"],
      data["value"],
      data["frequency"],
      submission["created_at"],
    ]);
  }

  return table_arr;
}

/**
 * EXPENSES
 */

function expensesActionColumn(_, row) {
  const html_content = `
      <div class="d-flex gap-2">
          <a class="btn btn-outline-info" href="/atualizar-status-despesa/?id=${row.cells[0].data}&status=Paga" title="Pagar conta">
              <i class="bi bi-box-arrow-in-up"></i>
          </a>
          <a class="btn btn-outline-secondary" href="/vizualizar-objeto/?id=${row.cells[0].data}" title="Vizualizar objeto">
              <i class="bi bi-ticket-detailed"></i>
          </a>
          <a class="btn btn-outline-primary" href="/editar-despesa/?id=${row.cells[0].data}" title="Editar despesa">
              <i class="bi bi-pencil"></i>
          </a>
          <a class="btn btn-outline-danger" href="/excluir-objeto/?id=${row.cells[0].data}" title="Excluir despesa">
              <i class="bi bi-trash"></i>
          </a>
      </div>  
        `;

  return html(html_content);
}

new gridjs.Grid({
  columns: [
    "ID",
    "Desc.",
    {
      name: "Valor",
      formatter: (_, row) => "$ " + row.cells[2].data,
    },
    "Freq.",
    {
      name: "Vencimento",
      formatter: (_, row) => getDateStr(row.cells[4].data),
    },
    "Status",
    "Tipo",
    {
      name: "Ações",
      formatter: expensesActionColumn,
    },
  ],
  data: fetchExpensesDataHadler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("wrapper_expenses"));

function fetchExpensesDataHadler() {
  console.log(expenses);
  const submissions = expenses;

  let table_arr = [];
  for (const submission of submissions) {
    const data = JSON.parse(submission.data);

    table_arr.push([
      submission["id"],
      data["desc"],
      data["value"],
      data["frequency"],
      data["bill_due"],
      data["status"],
      data["type"],
    ]);
  }

  return table_arr;
}

/**
 * Cash Flow
 */

function cashFlowActionColumn(_, row) {
  const html_content = `
      <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="/vizualizar-objeto/?id=${row.cells[0].data}" title="Vizualizar objeto">
              <i class="bi bi-ticket-detailed"></i>
          </a>
          <a class="btn btn-outline-danger" href="/excluir-objeto/?id=${row.cells[0].data}" title="Excluir despesa">
              <i class="bi bi-trash"></i>
          </a>
      </div>  
        `;

  return html(html_content);
}

new gridjs.Grid({
  columns: [
    "ID",
    "Desc.",
    {
      name: "Valor",
      formatter: (_, row) => "$ " + row.cells[2].data,
    },
    "Tipo",
    {
      name: "Ações",
      formatter: cashFlowActionColumn,
    },
  ],
  data: fetchCashFlowsDataHadler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("wrapper_cash_flow"));

function fetchCashFlowsDataHadler() {
  console.log(cash_flows);
  const submissions = cash_flows;

  let table_arr = [];
  for (const submission of submissions) {
    const data = JSON.parse(submission.data);

    table_arr.push([
      submission["id"],
      data["desc"],
      data["value"],
      data["type"],
    ]);
  }

  return table_arr;
}

/**
 * EXTRAS
 */

function getDateStr(s) {
  const d = new Date(s);

  return d.toLocaleString().split(",")[0];
}
