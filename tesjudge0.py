import requests

url = "https://judge0.next-level-study.com/submissions?wait=true"

payload = {
    "source_code": "#include <stdio.h>\nint main(){printf(\"OK\");}",
    "language_id": 50
}

headers = {
    "Content-Type": "application/json"
}

response = requests.post(url, json=payload, headers=headers)

print("Status Code:", response.status_code)
print("Response:", response.json())