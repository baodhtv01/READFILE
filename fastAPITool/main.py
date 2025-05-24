from fastapi import FastAPI
from fastapi.openapi.utils import get_openapi
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import mysql.connector
import os

app = FastAPI()

# CORS (nếu cần)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Kết nối MySQL
MYSQL_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_DATABASE', '')
}

def get_mysql_connection():
    return mysql.connector.connect(**MYSQL_CONFIG)

class Item(BaseModel):
    name: str

@app.get("/")
def read_root():
    return {"message": "Hello, FastAPI is working!"}

@app.get("/items")
def get_items():
    conn = get_mysql_connection()
    cursor = conn.cursor()
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    cursor.close()
    conn.close()
    return {"tables": tables}

@app.post("/items")
def create_item(item: Item):
    # Demo insert (giả sử có bảng items)
    conn = get_mysql_connection()
    cursor = conn.cursor()
    cursor.execute("INSERT INTO items (name) VALUES (%s)", (item.name,))
    conn.commit()
    cursor.close()
    conn.close()
    return {"message": "Item created", "item": item}

# Swagger UI mặc định ở /docs
# OpenAPI schema ở /openapi.json
