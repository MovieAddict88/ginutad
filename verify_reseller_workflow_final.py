import os
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    # Reset database and create reseller
    os.system("mysql -u cornerst_vpn -pcornerst_vpn cornerst_vpn < setup.sql")
    os.system("php install.php")
    os.system("php setup_test_user.php")

    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Login
        page.goto("http://localhost:8080/login.php")
        page.fill('input[name="username"]', "reseller")
        page.fill('input[name="password"]', "reseller123")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php", timeout=60000)
        print("Login successful.")

        # Add credits
        page.click("a:has-text('Add Credits')")
        page.wait_for_url("http://localhost:8080/add_credits.php")
        page.fill('input[name="amount"]', "50")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php")
        print("Credits added successfully.")

        # Verify credits were added
        expect(page.locator(".card", has_text="Credit Balance").locator(".card-title")).to_have_text("₱150.00")
        print("Credit verification successful.")

        # Add a new client
        page.click("a:has-text('Add New Client')")
        page.wait_for_url("http://localhost:8080/add_client.php")
        page.fill('input[name="username"]', "testclient")
        page.fill('input[name="password"]', "testpassword")
        page.fill('input[name="expiration_date"]', "2025-12-31")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php")
        print("Client added successfully.")

        # More specific locators
        client_management_table = page.locator(".card", has_text="Client Management")

        # Verify client was added
        expect(client_management_table.locator("tr:has-text('testclient')")).to_be_visible()
        expect(page.locator(".card", has_text="Total Clients").locator(".card-text")).to_have_text("1")
        expect(page.locator(".card", has_text="Credit Balance").locator(".card-title")).to_have_text("₱140.00")
        print("Client verification successful.")

        # Edit the client
        client_management_table.locator("tr:has-text('testclient') a:has-text('Edit')").click()
        page.wait_for_url(lambda url: "edit_client.php" in url)
        page.fill('input[name="username"]', "testclient-edited")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php")
        print("Client edited successfully.")

        # Verify client was edited
        expect(client_management_table.locator("tr:has-text('testclient-edited')")).to_be_visible()
        print("Edit verification successful.")

        # Delete the client
        client_management_table.locator("tr:has-text('testclient-edited') a:has-text('Delete')").click()
        page.wait_for_url(lambda url: "delete_client.php" in url)
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php")
        print("Client deleted successfully.")

        # Verify client was deleted
        expect(client_management_table.locator("tr:has-text('testclient-edited')")).not_to_be_visible()
        expect(page.locator(".card", has_text="Total Clients").locator(".card-text")).to_have_text("0")
        print("Delete verification successful.")

        page.screenshot(path="/home/jules/verification/reseller_workflow_final.png")
        print("Verification successful!")

    except Exception as e:
        print(f"Verification failed: {e}")
        print(page.content())
        page.screenshot(path="/home/jules/verification/reseller_workflow_error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
