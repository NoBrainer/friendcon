# Data Access Objects (DAO)

## Handling Edge Cases
- Invalid function parameters: throws `BadFunctionCallException`
    - Example: Trying to set a global without a `name`
- Invalid state change: throws `LogicException`
    - Example: Trying to update global that hasn't been created
- Insufficient Access: returns null
    - Example: Trying to update game globals without game admin privileges
- Failed to get/set data: returns false
    - Example: No data returned
    - Example: Nothing changed
  