
import os
from playwright.sync_api import sync_playwright, expect

def verify_frontend(page):
    # Login as admin
    page.goto("http://localhost:8080/login.php")
    page.fill('input[name="username"]', "admin")
    page.fill('input[name="password"]', "admin123")
    page.click('input[type="submit"]')
    page.wait_for_url("http://localhost:8080/dashboard.php", timeout=60000)
    print("Admin login successful.")

    # Navigate to Accounts Management
    page.click("a:has-text('Accounts Management')")
    page.wait_for_url("http://localhost:8080/accounts.php")
    print("Navigated to Accounts Management.")

    # Add a new account
    page.fill('input[name="name"]', "Test Account")
    page.fill('input[name="price"]', "123.45")
    page.click('input[type="submit"][value="Add Account"]')
    print("New account added.")

    # Navigate to Reseller Management
    page.click("a:has-text('Reseller Management')")
    page.wait_for_url("http://localhost:8080/reseller_management.php")
    print("Navigated to Reseller Management.")

    # Click the "Add Reseller" button to navigate to the new page
    page.click("a:has-text('Add Reseller')")
    page.wait_for_url("http://localhost:8080/add_reseller.php")
    print("Navigated to Add Reseller page.")

    # Fill out the form
    page.fill('input[name="username"]', "newreseller")
    page.fill('input[name="password"]', "password123")
    page.fill('input[name="first_name"]', "New")
    page.fill('input[name="address"]', "123 New St")
    page.fill('input[name="contact_number"]', "555-5555")
    page.select_option("select[name='account_id']", value='1')
    page.fill('input[name="quantity"]', "5")
    page.click('input[type="submit"]')
    page.wait_for_url("http://localhost:8080/reseller_management.php")
    print("New reseller created.")

    # Verify the new reseller is in the list
    expect(page.locator("tr:has-text('New')")).to_be_visible()
    print("Reseller verification successful.")

    # Click the "Edit" button for the new reseller
    page.click("tr:has-text('New') a:has-text('Edit')")
    page.wait_for_url(lambda url: "edit_reseller.php" in url)
    print("Navigated to Edit Reseller page.")

    # Verify the heading on the edit page and pre-filled form value
    expect(page.locator("h2:has-text('Edit Reseller')")).to_be_visible()
    expect(page.locator('input[name="first_name"]')).to_have_value("New")
    print("Edit Reseller page verification successful.")

    # Take a screenshot of the edit page
    page.screenshot(path="/home/jules/verification/edit_reseller_page.png")

    # Update the reseller's name and password
    page.fill('input[name="first_name"]', "New-Updated")
    page.fill('input[name="password"]', "newpassword123")
    page.click('input[value="Update Reseller"]')
    print("Submitted reseller update form.")

    # Wait for redirection and verify the update
    page.wait_for_url(lambda url: "reseller_management.php" in url)
    expect(page.locator("tr:has-text('New-Updated')")).to_be_visible()
    print("Reseller update successfully verified on management page.")

    # Take screenshot of the management page with the updated reseller
    page.screenshot(path="/home/jules/verification/reseller_management_after_edit.png")

    # Log out from admin
    page.locator("a:has-text('Logout')").click()
    page.wait_for_url("http://localhost:8080/login.php")
    print("Logged out from admin.")

    # Log in as the updated reseller with the new password
    page.fill('input[name="username"]', "newreseller")
    page.fill('input[name="password"]', "newpassword123")
    page.click('input[type="submit"]')
    page.wait_for_url("http://localhost:8080/reseller_dashboard.php", timeout=60000)
    print("Successfully logged in as updated reseller with new password.")

    page.screenshot(path="/home/jules/verification/reseller_dashboard_final.png")
    print("Frontend verification successful!")

def run(playwright):
    # Reset database and create account and reseller
    os.system("sudo mysql -e \"DROP DATABASE IF EXISTS cornerst_vpn;\"")
    os.system("sudo mysql -e \"DROP USER IF EXISTS 'cornerst_vpn'@'localhost';\"")
    os.system("sudo mysql -e \"CREATE DATABASE cornerst_vpn;\"")
    os.system("sudo mysql -e \"CREATE USER 'cornerst_vpn'@'localhost' IDENTIFIED BY 'cornerst_vpn';\"")
    os.system("sudo mysql -e \"GRANT ALL PRIVILEGES ON cornerst_vpn.* TO 'cornerst_vpn'@'localhost';\"")
    os.system("sudo mysql -e \"FLUSH PRIVILEGES;\"")
    os.system("sudo mysql cornerst_vpn < setup.sql")
    os.system("php install.php")
    os.system("php -r 'require_once \"db_config.php\"; $name = \"Premium\"; $price = 10.00; $stmt = $pdo->prepare(\"INSERT INTO accounts (name, price) VALUES (?, ?)\"); $stmt->execute([$name, $price]);'")
    os.system("php -r 'require_once \"db_config.php\"; $username = \"reseller\"; $password = password_hash(\"reseller123\", PASSWORD_DEFAULT); $role = \"reseller\"; $is_reseller = 1; $first_name = \"Test\"; $last_name = \"Reseller\"; $address = \"123 Test St\"; $contact_number = \"555-1234\"; $credits = 100.00; $account_id = 1; $client_limit = 10; $stmt = $pdo->prepare(\"INSERT INTO users (username, password, role, is_reseller, credits, first_name, last_name, address, contact_number, account_id, client_limit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\"); $stmt->execute([$username, $password, $role, $is_reseller, $credits, $first_name, $last_name, $address, $contact_number, $account_id, $client_limit]);'")

    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Reseller workflow test
        page.goto("http://localhost:8080/login.php")
        page.fill('input[name="username"]', "reseller")
        page.fill('input[name="password"]', "reseller123")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_dashboard.php", timeout=60000)
        print("Login successful.")

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
        expect(page.locator(".card", has_text="Credit Balance").locator(".card-title")).to_have_text("₱90.00")
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

        page.screenshot(path="/home/jules/verification/reseller_workflow.png")
        print("Verification successful!")

    except Exception as e:
        print(f"Verification failed: {e}")
        print(page.content())
        page.screenshot(path="/home/jules/verification/reseller_workflow_error.png")

    finally:
        browser.close()

    # Verify frontend changes
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()
    try:
        verify_frontend(page)
    except Exception as e:
        print(f"Frontend verification failed: {e}")
        print(page.content())
        page.screenshot(path="/home/jules/verification/frontend_verification_error.png")
    finally:
        browser.close()


if __name__ == "__main__":
    with sync_playwright() as playwright:
        run(playwright)
