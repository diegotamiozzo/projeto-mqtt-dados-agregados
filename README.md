# Projeto de Agregação de Dados de Telemetria (MQTT)

Este projeto é uma aplicação web robusta, construída com o framework Laravel, dedicada à ingestão, processamento e agregação de dados de telemetria (leituras de equipamentos) provenientes de fontes como MQTT. O foco principal é transformar grandes volumes de dados brutos em informações sumarizadas por períodos (hora ou dia), otimizando o desempenho de consultas e a visualização de longo prazo.

##  Tecnologias Utilizadas

O projeto utiliza um *stack* moderno e eficiente para garantir escalabilidade e uma experiência de desenvolvimento ágil.

| Categoria | Tecnologia | Versão Principal | Propósito |
| :--- | :--- | :--- | :--- |
| **Backend** | Laravel | ^12.0 | Framework PHP para lógica de negócios e API. |
| **Linguagem** | PHP | ^8.2 | Linguagem principal do servidor. |
| **Frontend** | Vite + TailwindCSS | ^4.0 | Empacotamento de assets e estilização utilitária. |
| **Visualização** | Chart.js | ^4.5 | Geração de gráficos e visualização de dados. |
| **Banco de Dados** | MySQL | - | Armazenamento de dados brutos e agregados. |

##  Funcionalidades Principais

### 1. Agregação de Leituras de Equipamentos

O coração do sistema é o comando Artisan `leituras:agregar`, que processa dados brutos e os consolida na tabela `dados_agregados`.

- **Tipos de Dados Processados:**
    - Correntes (Brunidores, Descascadores, Polidores).
    - Temperaturas.
    - Umidades.
    - Grandezas Elétricas (Tensão, Corrente, Potência Ativa/Reativa/Aparente, Fator de Potência).
- **Agregação Otimizada:** O processo é executado em lotes (`BATCH_SIZE = 500`) utilizando a função `upsert` do banco de dados para alta performance na inserção/atualização de dados agregados.
- **Cálculos:** Para cada período, são calculados:
    - Média (`AVG`).
    - Máximo (`MAX`).
    - Mínimo (`MIN`).
    - Última Leitura (`ultima`).

### 2. Agregação por Período

O comando de agregação suporta dois modos de operação:

| Período | Parâmetro | Descrição |
| :--- | :--- | :--- |
| **Hora** | `--periodo=hora` | Agrega leituras em intervalos de 1 hora. |
| **Dia** | `--periodo=dia` | Agrega leituras em intervalos de 1 dia. |

### 3. Gerenciamento de Dados Brutos

- **Marcação de Processamento:** Registros brutos processados são marcados com a *flag* `agregado = 1`.
- **Limpeza Automática:** O sistema executa uma rotina de limpeza que remove registros brutos marcados como agregados e que são mais antigos que 30 dias, garantindo a manutenção do banco de dados.

### 4. Interface Web e API

- **Visualização:** Possui uma interface web (`LeiturasController`) para visualização dos dados agregados, utilizando componentes Blade e Chart.js.
- **Segurança da API:** Inclui um *middleware* (`ValidateExternalToken`) para proteger endpoints de ingestão de dados externos, garantindo que apenas clientes autorizados possam enviar leituras.

##  Instalação e Configuração

### Pré-requisitos

Você precisará ter instalado em seu ambiente:

- PHP (versão 8.2 ou superior)
- Composer
- Node.js e npm/pnpm
- Um servidor de banco de dados MySQL

### Passos de Instalação

O projeto inclui um *script* de `setup` no `composer.json` que automatiza a maioria das etapas:

1. **Clonar o Repositório:**
   ```bash
   git clone https://github.com/diegotamiozzo/projeto-mqtt-dados-agregados.git
   cd projeto-mqtt-dados-agregados
   ```

2. **Executar o Setup:**
   Este comando irá instalar dependências PHP e Node, configurar o arquivo `.env`, gerar a chave da aplicação e executar as migrações do banco de dados.
   ```bash
   composer setup
   ```
   *Nota: Certifique-se de configurar as credenciais do banco de dados no arquivo `.env` antes de executar as migrações.*

3. **Executar o Servidor de Desenvolvimento:**
   O *script* `dev` inicia o servidor Laravel, o *listener* de fila, o *logger* (Pail) e o Vite em paralelo.
   ```bash
   composer dev
   ```

## Uso do Comando de Agregação

Para executar a agregação de dados, utilize o comando Artisan:

### Agregação por Hora (Recomendado para execução frequente)

```bash
php artisan leituras:agregar --periodo=hora
```

### Agregação Manual (Para testes ou reprocessamento)

```bash
php artisan leituras:agregar
# O padrão é --periodo=hora
```

## Estrutura de Dados Chave

| Tabela | Propósito | Chaves de Agregação |
| :--- | :--- | :--- |
| `corrente_*`, `temperaturas`, `umidades`, `grandezas_eletricas` | Armazenamento de **dados brutos** de telemetria. | `id_cliente`, `id_equipamento`, `timestamp` |
| `dados_agregados` | Armazenamento de **dados sumarizados** (média, max, min, última leitura) por período. | `id_cliente`, `id_equipamento`, `periodo_inicio` |
| `users` | Gerenciamento de usuários e clientes. | `external_client_id` (para mapeamento externo) |

---