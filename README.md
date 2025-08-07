## Invoice Structure:

The invoice should contain the following fields:
* **Invoice ID**: Auto-generated during creation.
* **Invoice Status**: Possible states include `draft,` `sending,` and `sent-to-client`.
* **Customer Name** 
* **Customer Email** 
* **Invoice Product Lines**, each with:
  * **Product Name**
  * **Quantity**: Integer, must be positive. 
  * **Unit Price**: Integer, must be positive.
  * **Total Unit Price**: Calculated as Quantity x Unit Price. 
* **Total Price**: Sum of all Total Unit Prices.

## Required Endpoints:

1. **View Invoice**: Retrieve invoice data in the format above.
2. **Create Invoice**: Initialize a new invoice.
3. **Send Invoice**: Handle the sending of an invoice.

## Functional Requirements:

### Invoice Criteria:

* An invoice can only be created in `draft` status. 
* An invoice can be created with empty product lines. 
* An invoice can only be sent if it is in `draft` status. 
* An invoice can only be marked as `sent-to-client` if its current status is `sending`. 
* To be sent, an invoice must contain product lines with both quantity and unit price as positive integers greater than **zero**.

### Invoice Sending Workflow:

* **Send an email notification** to the customer using the `NotificationFacade`. 
  * The email's subject and message may be hardcoded or customized as needed. 
  * Change the **Invoice Status** to `sending` after sending the notification.

### Delivery:

* Upon successful delivery by the Dummy notification provider:
  * The **Notification Module** triggers a `ResourceDeliveredEvent` via webhook.
  * The **Invoice Module** listens for and captures this event.
  * The **Invoice Status** is updated from `sending` to `sent-to-client`.
  * **Note**: This transition requires that the invoice is currently in the `sending` status.

## Technical Requirements:

* **Preferred Approach**: Domain-Driven Design (DDD) is preferred for this project. If you have experience with DDD, please feel free to apply this methodology. However, if you are more comfortable with another approach, you may choose an alternative structure.
* **Alternative Submission**: If you have a different, comparable project or task that showcases your skills, you may submit that instead of creating this task.
* **Unit Tests**: Core invoice logic should be unit tested. Testing the returned values from endpoints is not required.
* **Documentation**: Candidates are encouraged to document their decisions and reasoning in comments or a README file, explaining why specific implementations or structures were chosen.

## Setup Instructions:

* Start the project by running `./start.sh`.
* To access the container environment, use: `docker compose exec app bash`.

## Architecture & Design Decisions

This project follows principles of Domain-Driven Design

### Key decisions:

- **Aggregates and Value Objects**  
  The `InvoiceAggregate` encapsulates business rules and state changes for the Invoice domain. This protects invariants and provides a clear entry point for applying domain behavior.

- **Eloquent as Persistence Layer, Not Entity**  
  Eloquent models are used for persistence only. Domain logic is encapsulated in aggregates and value objects to keep the domain layer decoupled from the framework.

- **Repository Pattern**  
  The `InvoiceRepositoryInterface` abstracts data access, allowing the domain and application layers to remain infrastructure-agnostic.

- **Events for Decoupling**  
  Events like `ResourceDeliveredEvent` are used to trigger side effects asynchronously. This improves modularity and separates concerns (e.g., notifying clients, updating invoice status).

- **NotificationFacade**  
  A simple facade is used to abstract the actual notification logic, which could be implemented via emails, push notifications, etc., without coupling the service layer to a specific channel.

- **DTOs for Data Transfer**  
  DTOs like `CreateInvoiceDTO` help pass structured input data into services and prevent tight coupling with transport-specific representations (e.g., request classes).

## Potential Improvements

While the current design meets the core requirements, there are several areas that could be further enhanced:

- **Introduce Domain Entities**  
  Currently, Eloquent models are used primarily for persistence. Introducing explicit domain entities would help separate the business logic from the infrastructure layer and enforce invariants more clearly. For example, a proper `Invoice` entity could encapsulate behaviors like adding product lines, calculating totals, or determining if the invoice is payable.

- **Authorization Layer**  
  The current implementation does not include any authorization rules. Introducing an authorization policy (e.g., via Laravel's policies or custom application layer checks) would help control access to invoice-related actions.

- **CQRS & Event Sourcing (optional)**  
  Although currently not implemented, this architecture is a good fit for a Command Query Responsibility Segregation (CQRS) approach, especially given the clear use of aggregates and domain events. Event sourcing could also be introduced later for auditability and rebuilding domain state.

- **Transactional Consistency**  
  If multiple aggregates or models are ever modified in a single operation (e.g., invoice + related payments), wrapping service logic in an application-level transaction boundary would ensure consistency.

- **Improve Test Coverage**  
  While unit tests are present, integration or application layer tests could help verify behavior across modules (e.g., creation flow, event-triggered state transitions).

- **Domain Errors and Result Objects**  
  Instead of throwing exceptions directly or relying on null returns, a more expressive result-handling strategy could be adopted, such as a `Result` type (Success/Error), to improve predictability and control flow.

- **Database Indexing**  
  The current schema does not define any database indexes. Adding appropriate indexes (e.g., on `status`, `customer_email`, or foreign keys like `invoice_id` in related tables) would improve query performance, especially in large datasets.

- **Audit Trail**  
  Storing domain events or creating an audit log for invoice changes could help with compliance and traceability.

- **API Documentation & Versioning**  
  For a real-world service, consider adding OpenAPI/Swagger documentation and versioning endpoints to ensure forward compatibility.
