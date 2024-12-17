import "@mantine/core/styles.css";
import {
  AppShell,
  Avatar,
  Burger,
  Flex,
  Group,
  MantineProvider,
  Menu,
  ScrollArea,
  Text,
  UnstyledButton,
} from "@mantine/core";
import {theme} from "./theme";
import {useDisclosure} from "@mantine/hooks";
import {useState} from "react";
import {SidebarMenu} from "./components/SidebarMenu";
import {IconDashboard, IconSettings, IconSitemap, IconUsers,} from "@tabler/icons-react";
import {BrowserRouter, Route, Routes} from "react-router";
import UserList from "./routes/admin/organization/users";

const menuConfig = [
  {label: "Dashboard", icon: IconDashboard, link: "/"},
  {
    label: "Administration",
    icon: IconSettings,
    children: [
      {
        label: "Organisation",
        icon: IconSitemap,
        children: [
          {
            label: "Utilisateurs",
            link: "/admin/organization/utilisateurs",
            icon: IconUsers,
          },
        ],
      },
    ],
  },
];

export default function App() {
  const [mobileOpened, {toggle: toggleMobile}] = useDisclosure();
  const [desktopOpened, {toggle: toggleDesktop}] = useDisclosure(true);

  const [, setUserMenuOpened] = useState(false);

  return (
    <MantineProvider theme={theme}>
      <BrowserRouter>
        <AppShell
          header={{height: 75}}
          navbar={{
            width: 300,
            breakpoint: "sm",
            collapsed: {mobile: !mobileOpened, desktop: !desktopOpened},
          }}
          padding="md"
        >
          <AppShell.Header>
            <Group justify="space-between">
              <Group h="100%" px="md">
                <Burger
                  opened={mobileOpened}
                  onClick={toggleMobile}
                  hiddenFrom="sm"
                  size="sm"
                />
                <Burger
                  opened={desktopOpened}
                  onClick={toggleDesktop}
                  visibleFrom="sm"
                  size="sm"
                />
                <Text>Logo</Text>
              </Group>
              <Flex
                h={70}
                gap="md"
                justify="flex-end"
                align="center"
                direction="row"
              >
                <Menu
                  width={260}
                  position="bottom-end"
                  transitionProps={{transition: "pop-top-right"}}
                  onClose={() => setUserMenuOpened(false)}
                  onOpen={() => setUserMenuOpened(true)}
                  withinPortal
                >
                  <Menu.Target>
                    <UnstyledButton>
                      <Avatar
                        mt="5"
                        me="5"
                        radius="xl"
                        size={50}
                        name={"TD"}
                        color="initials"
                      />
                    </UnstyledButton>
                  </Menu.Target>
                  <Menu.Dropdown>
                    <Menu.Item color="red">
                      {"label.header.profile.menu.logout"}
                    </Menu.Item>
                  </Menu.Dropdown>
                </Menu>
              </Flex>
            </Group>
          </AppShell.Header>
          <AppShell.Navbar p="md">
            <AppShell.Section>Navbar header</AppShell.Section>
            <AppShell.Section grow my="md" component={ScrollArea}>
              <SidebarMenu items={menuConfig}/>
            </AppShell.Section>
            <AppShell.Section>Copyright - 2024</AppShell.Section>
          </AppShell.Navbar>
          <AppShell.Main>
            <Routes>
              <Route path="/" element={<h1>Dashboard</h1>}/>
              <Route path="/admin">
                <Route path="organization">
                  <Route path="utilisateurs" element={<UserList/>}/>
                </Route>
              </Route>
            </Routes>
          </AppShell.Main>
        </AppShell>
      </BrowserRouter>
    </MantineProvider>
  );
}
