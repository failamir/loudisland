use sqlx::{mysql::MySqlPoolOptions, MySql, Pool};
use tracing::{error, info};

pub type DbPool = Pool<MySql>;

pub async fn init_pool(database_url: &str) -> Result<DbPool, sqlx::Error> {
    info!("Connecting to MySQL database: {}", database_url);
    let pool = MySqlPoolOptions::new()
        .max_connections(10)
        .connect(database_url)
        .await?;
    info!("Successfully connected to MySQL database!");
    Ok(pool)
}
