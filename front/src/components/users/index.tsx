import { Table, Title } from "@mantine/core";

export default function Users() {
	return (
		<>
			<Title>Users</Title>
			<Table>
				<Table.Thead>
					<Table.Tr>
						<Table.Th>First name</Table.Th>
						<Table.Th>Last name</Table.Th>
						<Table.Th>Email</Table.Th>
					</Table.Tr>
				</Table.Thead>
				<Table.Tbody>
					<Table.Tr>
						<Table.Td>John</Table.Td>
						<Table.Td>Doe</Table.Td>
						<Table.Td>john@doe.lol</Table.Td>
					</Table.Tr>
				</Table.Tbody>
			</Table>
		</>
	);
}
