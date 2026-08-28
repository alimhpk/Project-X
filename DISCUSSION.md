# Discussion

## Section 1: Project Setup

I added the local setup and CI checks needed to install, build, and test the project consistently. I stayed close to Laravel’s standard structure because the project did not need a custom setup.

Local tests use SQLite because it is quick and simple. CI uses MySQL so database behaviour is also checked against the database used by the project. The downside is that SQLite and MySQL can behave differently, but using MySQL in CI helps catch those differences.

## Section 2: Database and API

I moved the destination data from the hardcoded array into the database. I added the model, factory, migrations, and a repeatable seeder. The seeder matches destinations by name and country, so it can run again without creating duplicates.

The API supports search, filtering, sorting, and pagination. I used an explicit list of allowed sort fields instead of passing query input directly to the database. API responses use camelCase, while database columns stay in snake_case.

The destination explorer also uses a database query with server-side pagination. I kept the database logic in the model and components instead of adding repository or service classes because the project is still small.

## Section 3: User Interface

I fixed the main problems in the destination explorer and improved the layout for different screen sizes. I added grid and list views, loading feedback, pagination, reset controls, and clear messages when no destinations are available or no results match the filters.

I also improved labels, keyboard focus, and the state of the view buttons. I kept the interface in Livewire instead of adding another JavaScript framework. This means some actions make a request to the server, but it keeps the application simpler and avoids managing the same state in two places.

## Section 4: Authentication

I protected the destination API with Laravel Sanctum. Tokens only receive permission to read destinations, and the API checks for that permission.

Tokens are issued and revoked through Artisan commands. I chose this instead of adding public token-management routes because token administration should not be publicly available. Tokens expire, and the plain token is only displayed when it is created.

The main limitation is that managing tokens requires command-line access. For this project that is acceptable, but a larger application may need a protected administration area.

## With More Time

With more time, I would add browser tests for the complete explorer flow and test the interface on more devices and browsers.

If the number of destinations became much larger, I would review full-text search and cursor pagination. I would also add better token activity records and a more complete token rotation process.
