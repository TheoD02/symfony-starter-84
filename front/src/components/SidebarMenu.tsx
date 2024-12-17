import { useEffect, useState } from "react";
import { IconChevronRight, IconCircle } from "@tabler/icons-react";
import { Box, Collapse, Group, ThemeIcon } from "@mantine/core";
import { Link, useLocation } from "react-router";
import classes from "./SidebarMenu.module.css";

interface MenuItem {
	label: string;
	link?: string;
	icon?: React.FC<any>;
	children?: MenuItem[];
}

interface SidebarMenuProps {
	items: MenuItem[];
}

export function SidebarMenu({ items }: SidebarMenuProps) {
	return (
		<Box>
			{items.map((item) => (
				<MenuItemComponent key={item.label} item={item} />
			))}
		</Box>
	);
}

function MenuItemComponent({ item }: { item: MenuItem }) {
	const [opened, setOpened] = useState(false);
	const hasChildren = Array.isArray(item.children) && item.children.length > 0;
	const location = useLocation();
	const isActive = location.pathname === item.link;

	const isParentActive = (children: MenuItem[]): boolean => {
		return children.some((child) => {
			if (child.link && location.pathname === child.link) {
				return true;
			}
			if (child.children) {
				return isParentActive(child.children);
			}
			return false;
		});
	};

	const activeClass =
		isActive || (hasChildren && isParentActive(item.children))
			? classes.active
			: "";

	useEffect(() => {
		if (hasChildren && isParentActive(item.children)) {
			setOpened(true);
		}
	}, [location.pathname, item.children]);

	return (
		<>
			<Link
				to={item.link || "#"}
				onClick={() => setOpened((o) => !o)}
				className={`${classes.control} ${activeClass}`}
			>
				<Group justify="space-between" gap={0}>
					<Box style={{ display: "flex", alignItems: "center" }}>
						<ThemeIcon variant="light" size={30}>
							{item.icon ? <item.icon size={18} /> : <IconCircle size={18} />}
						</ThemeIcon>
						<Box ml="md">{item.label}</Box>
					</Box>
					{hasChildren && (
						<IconChevronRight
							className={classes.chevron}
							stroke={1.5}
							size={16}
							style={{ transform: opened ? "rotate(-90deg)" : "none" }}
						/>
					)}
				</Group>
			</Link>
			{hasChildren && (
				<Collapse in={opened}>
					<Box className={classes.nested}>
						{item.children.map((child) => (
							<MenuItemComponent key={child.label} item={child} />
						))}
					</Box>
				</Collapse>
			)}
		</>
	);
}
